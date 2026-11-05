<?php

namespace App\Http\Controllers;

use App\Models\CourseSignature;
use Illuminate\Http\Request;

class CertificateController extends Controller
{
    /**
     * Muestra la vista de verificación de un certificado por folio.
     */
    public function verify(string $folio)
    {
        $signature = CourseSignature::where('folio', $folio)->with('user')->first();

        return view('certificate.verify', [
            'signature' => $signature,
            'isValid' => $signature?->isValid() ?? false,
        ]);
    }
}
