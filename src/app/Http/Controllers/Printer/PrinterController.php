<?php

namespace App\Http\Controllers\Printer;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PrinterController extends Controller
{
    /**
     * Save User Preview Settings
     */
    public function savePreviewSettings(Request $request)
    {
        $previewUsers = $request->input('preview_users', []);

        // Update each user's preview_receipt setting
        foreach ($previewUsers as $userId => $value) {
            User::where('id', $userId)->update([
                'preview_receipt' => true
            ]);
        }

        // Set preview_receipt = false for users who were NOT checked
        User::whereNotIn('id', array_keys($previewUsers))
             ->update(['preview_receipt' => false]);

        return redirect()->back()
                         ->with('success', 'Preview settings saved successfully!');
    }
}
