<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>PDF Editor</title>

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">

    <style>
        #pdfContainer {
            position: relative;
            border: 1px solid #ddd;
            display: inline-block;
            background: #fff;
        }

        #pdfCanvas {
            display: block;
        }

        .stamp {
            cursor: move;
            touch-action: none;
            user-select: none;
        }
    </style>
</head>

<body>
    <div class="container mt-4">
        <h3>PDF Editor (Upload + Stamp QR/Image)</h3>

        {{-- Upload PDF --}}
        <div class="card mb-3">
            <div class="card-body">
                <form id="pdfUploadForm">
                    @csrf
                    <input type="file" name="pdf" accept="application/pdf" required>
                    <button class="btn btn-primary btn-sm">Upload PDF</button>
                </form>
            </div>
        </div>

        {{-- Tools (hidden until PDF uploaded) --}}
        <div id="tools" class="card mb-3" style="display:none;">
            <div class="card-body">
                <div class="form-inline mb-2">
                    <input id="qrText" class="form-control mr-2" style="width:300px;" placeholder="QR text/URL">
                    <button id="btnGenQR" type="button" class="btn btn-success btn-sm mr-3">Generate QR</button>

                    <form id="imgUploadForm" class="form-inline">
                        @csrf
                        <input type="file" name="image" accept="image/png" required>
                        <button class="btn btn-info btn-sm" type="submit">Upload PNG</button>
                    </form>
                </div>
                <small class="text-muted">Drag/resize QR or PNG on the PDF. Use Prev/Next for other pages. Then
                    Save.</small>
            </div>
        </div>

        {{-- Pager --}}
        <div id="pager" style="display:none;" class="mb-2">
            <button id="btnPrev" type="button" class="btn btn-secondary btn-sm">Prev</button>
            <span id="pageInfo" class="mx-2"></span>
            <button id="btnNext" type="button" class="btn btn-secondary btn-sm">Next</button>
        </div>

        {{-- PDF Preview --}}
        <div id="pdfArea" class="card" style="display:none;">
            <div class="card-body">
                <div id="pdfContainer">
                    <canvas id="pdfCanvas"></canvas>
                    {{-- stamps will be appended here --}}
                </div>
            </div>
        </div>

        <div class="mt-3" id="saveBar" style="display:none;">
            <button id="btnSave" type="button" class="btn btn-primary">Save & Download Final PDF</button>
        </div>
    </div>

    {{-- libs --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/interact.js/1.10.27/interact.min.js"></script>

    <script>
        let pdfFile = null;
        let pdfDoc = null;
        let currentPage = 1;
        let scale = 1.5;
        let pageViewport = null;

        // { pageNo: [stampObj, ...] }
        let stampsByPage = {};
        let activeStamps = [];

        pdfjsLib.GlobalWorkerOptions.workerSrc =
            "https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js";

        // Upload PDF
        document.getElementById('pdfUploadForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            let fd = new FormData(e.target);

            let res = await fetch("{{ route('pdf.upload') }}", {
                method: "POST",
                body: fd
            });
            let json = await res.json();
            if (!json.ok) return alert("Upload failed");

            pdfFile = json.pdf_file;
            await loadPdf(json.pdf_url);

            document.getElementById('tools').style.display = "block";
            document.getElementById('pager').style.display = "block";
            document.getElementById('pdfArea').style.display = "block";
            document.getElementById('saveBar').style.display = "block";
        });

        async function loadPdf(url) {
            pdfDoc = await pdfjsLib.getDocument(url).promise;
            stampsByPage = {};
            currentPage = 1;
            await renderPage(currentPage);
            updatePager();
        }

        async function renderPage(pageNo) {
            currentPage = pageNo;
            let page = await pdfDoc.getPage(pageNo);
            pageViewport = page.getViewport({
                scale
            });

            let canvas = document.getElementById('pdfCanvas');
            let ctx = canvas.getContext('2d');
            canvas.width = pageViewport.width;
            canvas.height = pageViewport.height;

            await page.render({
                canvasContext: ctx,
                viewport: pageViewport
            }).promise;

            showPageStamps(pageNo);
            updatePager();
        }

        function updatePager() {
            document.getElementById("pageInfo").innerText =
                `Page ${currentPage} / ${pdfDoc.numPages}`;

            document.getElementById("btnPrev").disabled = (currentPage <= 1);
            document.getElementById("btnNext").disabled = (currentPage >= pdfDoc.numPages);
        }

        // Prev / Next
        document.getElementById("btnPrev").addEventListener("click", () => {
            if (currentPage > 1) renderPage(currentPage - 1);
        });
        document.getElementById("btnNext").addEventListener("click", () => {
            if (currentPage < pdfDoc.numPages) renderPage(currentPage + 1);
        });

        // Show only stamps for this page
        function showPageStamps(pageNo) {
            activeStamps.forEach(s => s.el.remove());
            activeStamps = [];

            let list = stampsByPage[pageNo] || [];
            let container = document.getElementById("pdfContainer");

            list.forEach(s => {
                container.appendChild(s.el);
                makeInteract(s.el, s);
                activeStamps.push(s);
            });
        }

        // Generate QR
        document.getElementById('btnGenQR').addEventListener('click', async () => {
            let text = document.getElementById('qrText').value.trim();
            if (!text) return alert("QR text required");

            let fd = new FormData();
            fd.append("_token", "{{ csrf_token() }}");
            fd.append("text", text);

            let res = await fetch("{{ route('qr.generate') }}", {
                method: "POST",
                body: fd
            });
            let json = await res.json();
            if (!json.ok) return alert("QR failed");

            addStamp(json.image_url, json.image_file);
        });

        // Upload PNG
        document.getElementById('imgUploadForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            let fd = new FormData(e.target);

            let res = await fetch("{{ route('image.upload') }}", {
                method: "POST",
                body: fd
            });
            let json = await res.json();
            if (!json.ok) return alert("Image upload failed");

            addStamp(json.image_url, json.image_file);
        });

        // Add stamp to current page
        function addStamp(url, imageFile) {
            let container = document.getElementById('pdfContainer');

            let img = document.createElement('img');
            img.src = url;
            img.style.position = "absolute";
            img.style.left = "20px";
            img.style.top = "20px";
            img.style.width = "120px";
            img.style.height = "120px";
            img.classList.add("stamp");

            let stampObj = {
                image_file: imageFile,
                page: currentPage,
                x: 20,
                y: 20,
                w: 120,
                h: 120,
                el: img
            };

            if (!stampsByPage[currentPage]) stampsByPage[currentPage] = [];
            stampsByPage[currentPage].push(stampObj);

            container.appendChild(img);
            makeInteract(img, stampObj);
            activeStamps.push(stampObj);
        }

        // Drag + resize
        function makeInteract(el, stamp) {
            interact(el)
                .draggable({
                    listeners: {
                        move(event) {
                            let x = (parseFloat(el.getAttribute('data-x')) || 0) + event.dx;
                            let y = (parseFloat(el.getAttribute('data-y')) || 0) + event.dy;

                            el.style.transform = `translate(${x}px, ${y}px)`;
                            el.setAttribute('data-x', x);
                            el.setAttribute('data-y', y);

                            stamp.x = 20 + x;
                            stamp.y = 20 + y;
                        }
                    }
                })
                .resizable({
                    edges: {
                        left: true,
                        right: true,
                        bottom: true,
                        top: true
                    },
                    listeners: {
                        move(event) {
                            let x = parseFloat(event.target.dataset.x) || 0;
                            let y = parseFloat(event.target.dataset.y) || 0;

                            event.target.style.width = event.rect.width + 'px';
                            event.target.style.height = event.rect.height + 'px';

                            x += event.deltaRect.left;
                            y += event.deltaRect.top;

                            event.target.style.transform = `translate(${x}px, ${y}px)`;
                            event.target.dataset.x = x;
                            event.target.dataset.y = y;

                            stamp.w = event.rect.width;
                            stamp.h = event.rect.height;
                            stamp.x = 20 + x;
                            stamp.y = 20 + y;
                        }
                    }
                });
        }

        // Save final PDF
        document.getElementById('btnSave').addEventListener('click', async () => {
            if (!pdfFile) return alert("No PDF");

            const canvas = document.getElementById('pdfCanvas');
            const cw = canvas.width;
            const ch = canvas.height;

            // real PDF size in points
            const pdfW = pageViewport.width / scale;
            const pdfH = pageViewport.height / scale;

            let allStamps = [];
            Object.keys(stampsByPage).forEach(p => {
                stampsByPage[p].forEach(s => allStamps.push(s));
            });

            let mapped = allStamps.map(s => {
                return {
                    image_file: s.image_file,
                    page: s.page,
                    x: (s.x / cw) * pdfW,
                    y: (s.y / ch) * pdfH,
                    w: (s.w / cw) * pdfW,
                    h: (s.h / ch) * pdfH,
                };
            });

            let res = await fetch("{{ route('pdf.save') }}", {
                method: "POST",
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    pdf_file: pdfFile,
                    stamps: mapped
                })
            });

            let json = await res.json();
            if (!json.ok) return alert("Save failed");

            window.location.href = json.download_url;
        });
    </script>
</body>

</html>
