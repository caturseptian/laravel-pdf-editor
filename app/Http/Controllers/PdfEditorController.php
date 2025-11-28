<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use setasign\Fpdi\Tcpdf\Fpdi;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class PdfEditorController extends Controller
{
    public function index()
    {
        return view('pdf-editor');
    }

    public function uploadPdf(Request $request)
    {
        $request->validate([
            'pdf' => 'required|mimes:pdf|max:10240'
        ]);

        $file = $request->file('pdf');
        $name = Str::uuid()->toString() . '.pdf';
        $path = $file->storeAs('pdfs', $name, 'public');

        return response()->json([
            'ok' => true,
            'pdf_url' => asset('storage/' . $path),
            'pdf_file' => $name,
        ]);
    }

    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|mimes:png|max:5120'
        ]);

        $file = $request->file('image');
        $name = Str::uuid()->toString() . '.png';
        $path = $file->storeAs('stamps', $name, 'public');

        return response()->json([
            'ok' => true,
            'image_url' => asset('storage/' . $path),
            'image_file' => $name,
        ]);
    }

    public function generateQr(Request $request)
    {
        $request->validate([
            'text' => 'required|string|max:500'
        ]);

        // Ensure stamps dir exists
        $dir = storage_path('app/public/stamps');
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $name = Str::uuid()->toString() . '.png';
        $path = $dir . '/' . $name;

        QrCode::format('png')
            ->size(300)
            ->margin(1)
            ->generate($request->text, $path);

        return response()->json([
            'ok' => true,
            'image_url' => asset('storage/stamps/' . $name),
            'image_file' => $name,
        ]);
    }

    public function save(Request $request)
    {
        $data = $request->validate([
            'pdf_file' => 'required|string',
            'stamps'   => 'required|array',
            'stamps.*.image_file' => 'required|string',
            'stamps.*.page'       => 'required|integer|min:1',
            'stamps.*.x'          => 'required|numeric',
            'stamps.*.y'          => 'required|numeric',
            'stamps.*.w'          => 'required|numeric',
            'stamps.*.h'          => 'required|numeric',
        ]);

        $pdfPath = storage_path('app/public/pdfs/' . $data['pdf_file']);
        if (!file_exists($pdfPath)) {
            return response()->json(['ok' => false, 'msg' => 'PDF not found'], 404);
        }

        // IMPORTANT: points unit + no margins
        $pdf = new Fpdi('P', 'pt');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(0, 0, 0);
        $pdf->SetAutoPageBreak(false, 0);

        $pageCount = $pdf->setSourceFile($pdfPath);

        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            $tpl  = $pdf->importPage($pageNo);
            $size = $pdf->getTemplateSize($tpl);

            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($tpl, 0, 0, $size['width'], $size['height'], true);

            foreach ($data['stamps'] as $s) {
                if ((int)$s['page'] !== $pageNo) continue;

                $imgPath = storage_path('app/public/stamps/' . $s['image_file']);
                if (!file_exists($imgPath)) continue;

                $pdf->Image(
                    $imgPath,
                    $s['x'],
                    $s['y'],
                    $s['w'],
                    $s['h'],
                    'PNG'
                );
            }
        }

        $finalDir = storage_path('app/public/finals');
        if (!is_dir($finalDir)) {
            mkdir($finalDir, 0775, true);
        }

        $finalName = Str::uuid()->toString() . '_final.pdf';
        $finalPath = $finalDir . '/' . $finalName;

        $pdf->Output($finalPath, 'F');

        return response()->json([
            'ok' => true,
            'final_file' => $finalName,
            'download_url' => route('pdf.download', ['file' => $finalName])
        ]);
    }

    public function download($file)
    {
        $path = storage_path('app/public/finals/' . $file);
        abort_unless(file_exists($path), 404);

        return response()->download($path, 'final.pdf');
    }
}
