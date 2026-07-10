<?php

namespace App\Http\Controllers\Admins;

use App\Http\Controllers\Controller;
use App\Services\GoogleSheetService;
use Illuminate\Http\Request;

class GoogleSheetController extends Controller
{
    public function __construct(
        protected GoogleSheetService $googleSheetService
    ) {}

    public function read(Request $request) {
        $validated = $request->validate([
            'sheet_id' => 'required',
            'range' => 'required',
        ]);

        $result = $this->googleSheetService->read($validated['sheet_id'], $validated['range']);
        
        return implode("\n", array_map(function($row) {
            return implode("\t", $row);
        }, $result));
    }
}
