<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Param;
use App\Models\GedSignature;
use Exception;
use Illuminate\Support\Facades\Log;

class SignApiController extends Controller
{
    public function closeTask(Request $request)
    {
        $channel = Log::build([
            'driver' => 'single',
            'path' => storage_path('logs/ged.log'),
        ]);
        $clientid = $request->server('HTTP_X_ADOBESIGN_CLIENTID');

        // Log::stack(['slack', $channel])->info("CLIENT " . $request->method());
        if ($request->isMethod('get')) {
            return response()->json([
                "xAdobeSignClientId" => $clientid
            ]);
        }

        Log::stack(['slack', $channel])->info("CLIENT $clientid.");
        if ($clientid != "UB7E5BXCXY") {
            abort(403);
        }

        $request_data = json_decode($request->getContent(), true);

        $agreement = $request_data && isset($request_data['agreement']) ? $request_data['agreement'] : null;

        if ($agreement && ($agreement['status'] == 'CANCELLED' || $agreement['status'] == 'SIGNED')) {
            $finished = Param::where([['param_code', 'Etat'], ['code', 'Terminée'], ['is_active', 1]])->pluck('id')->first();
            $ged_signature = GedSignature::where('ged_doc_id', $agreement['id'])->first();
            try {
                if ($ged_signature) {
                    $ged_signature->ged_doc_state = $agreement['status'];
                    $ged_signature->save();
                    $task = $ged_signature->task;
                }
                if (isset($task) && $finished) {
                    $task->etat_id = $finished;
                    $task->save();
                    Log::stack(['slack', $channel])->info("CLIENT $clientid: TACHE ASSOCIEE AU CONTRAT " . $agreement['id'] . " A ETE TERMINE.");
                }
            } catch (Exception $e) {
                Log::stack(['slack', $channel])->info("ERROR {$e->getMessage()}");
            }
        }

        return response()->json([
            "xAdobeSignClientId" => $clientid
        ]);
    }
}
