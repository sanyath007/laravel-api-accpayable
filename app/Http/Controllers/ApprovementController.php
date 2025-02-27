<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;

use App\Models\Approvement;
use App\Models\ApprovementDetail;
use App\Models\Creditor;
use App\Models\Budget;
use App\Models\Debt;
use App\Models\DebtType;

class ApprovementController extends Controller
{
    /** สถานะ 0=รอดำเนินการ,1=ขออนุมัติ,2=ชำระเงินแล้ว,3=ยกเลิก */

    public function list()
    {
    	return view('approvements.list');
    }

    public function search($creditor, $sdate, $edate, $showall)
    {
        if($showall == 1) {
            if($creditor == 0) {
                $approvements = Approvement::whereIn('app_stat', [0 ,1])->paginate(10);
            } else {
                $approvements = Approvement::whereIn('app_stat', [0 ,1])
                                            ->where('supplier_id', '=', $creditor)
                                            ->paginate(10);
            }
        } else {
            if($creditor == 0) {
                $approvements = Approvement::whereIn('app_stat', [0 ,1])
                                            ->whereBetween('app_date', [$sdate, $edate])
                                            ->paginate(10);
            } else {
                $approvements = Approvement::whereIn('app_stat', [0 ,1])
                                            ->where('supplier_id', '=', $creditor)
                                            ->whereBetween('app_date', [$sdate, $edate])
                                            ->paginate(10);
            }
        }

        $approvement_debts = [];
        foreach ($approvements as $app) {
            $debts = ApprovementDetail::where(['app_id' => $app->app_id])->get();

            $ad = [];
            foreach ($debts as $debt) {
                array_push($ad, $debt->debt_id);
            }

            $approvement_debts[$app->app_id] = $ad;
        }

        return [
            'approvements' => $approvements,
            'approvement_debts' => $approvement_debts,
        ];
    }

    private function generateAutoId()
    {
        $app = \DB::table('nrhosp_acc_app')
                        ->select('app_id')
                        ->orderBy('app_id', 'DESC')
                        ->first();

        $startId = 'AP'.substr((date('Y') + 543), 2);
        $tmpLastId =  ((int)(substr($app->app_id, 4))) + 1;
        $lastId = $startId.sprintf("%'.07d", $tmpLastId);

        return $lastId;
    }

    public function store(Request $req)
    {
        $approvement = new Approvement();
        $approvement->app_id = $this->generateAutoId();
        $approvement->app_doc_no = $req['app_doc_no'];
        $approvement->app_date = $req['app_date'];
        $approvement->app_recdoc_no = $req['app_recdoc_no'];
        $approvement->app_recdoc_date = $req['app_recdoc_date'];

        $approvement->supplier_id = $req['supplier'];
        $approvement->pay_to = $req['pay_to'];
        $approvement->budget_id = $req['budget'];

        $approvement->amount = floatval(str_replace(",", "", $req['amount']));
        $approvement->tax_val = floatval(str_replace(",", "", $req['tax_val']));
        $approvement->discount = floatval(str_replace(",", "", $req['discount']));
        $approvement->fine = floatval(str_replace(",", "", $req['fine']));
        $approvement->vatrate = $req['vatrate'];
        $approvement->vatamt = floatval(str_replace(",", "", $req['vatamt']));
        $approvement->net_val = floatval(str_replace(",", "", $req['net_val']));
        $approvement->net_amt = floatval(str_replace(",", "", $req['net_amt']));
        $approvement->net_amt_str = $req['net_amt_str'];
        $approvement->net_total = floatval(str_replace(",", "", $req['net_total']));
        $approvement->net_total_str = $req['net_total_str'];
        $approvement->cheque = floatval(str_replace(",", "", $req['cheque']));
        $approvement->cheque_str = $req['cheque_str'];
        /** user info */
        $approvement->cr_userid = $req['cr_user'];
        $approvement->cr_date = date("Y-m-d H:i:s");
        $approvement->chg_userid = $req['chg_user'];
        $approvement->chg_date = date("Y-m-d H:i:s");
        
        $approvement->app_stat = '0';
        $approvement->is_approve = 'N';

        if($approvement->save()) {
            $index = 0;
            foreach ($req['debts'] as $debt) {
                /** Added Approvement Detail */
                $detail = new ApprovementDetail();
                $detail->app_id = $approvement->app_id;
                $detail->debt_id = $debt['debt_id'];
                $detail->seq_no = ++$index;
                $detail->is_paid = 'N';
                $detail->app_detail_stat = '0';
                $detail->save();

                /** Updated debt status to 1 */
                Debt::find($debt['debt_id'])->update(['debt_status' => 1]);
            }

            return [
                "status"    => "success",
                "message"   => "Insert success.",
            ];
        } else {
            return [
                "status" => "error",
                "message" => "Insert failed.",
            ];
        }
    }

    public function getById($appId)
    {
        return [
            'approvement' => Approvement::find($appId),
            'detail' => ApprovementDetail::where('app_id', $appId)->with('debt')->get(),
        ];
    }

    public function update(Request $req, $appId)
    {
        $approvement = Approvement::find($appId);

        $approvement->app_doc_no = $req['app_doc_no'];
        $approvement->app_date = $req['app_date'];
        $approvement->app_recdoc_no = $req['app_recdoc_no'];
        $approvement->app_recdoc_date = $req['app_recdoc_date'];

        $approvement->supplier_id = $req['supplier'];
        $approvement->pay_to = $req['pay_to'];
        $approvement->budget_id = $req['budget'];

        $approvement->amount = floatval(str_replace(",", "", $req['amount']));
        $approvement->tax_val = floatval(str_replace(",", "", $req['tax_val']));
        $approvement->discount = floatval(str_replace(",", "", $req['discount']));
        $approvement->fine = floatval(str_replace(",", "", $req['fine']));
        $approvement->vatrate = $req['vatrate'];
        $approvement->vatamt = floatval(str_replace(",", "", $req['vatamt']));
        $approvement->net_val = floatval(str_replace(",", "", $req['net_val']));
        $approvement->net_amt = floatval(str_replace(",", "", $req['net_amt']));
        $approvement->net_amt_str = $req['net_amt_str'];
        $approvement->net_total = floatval(str_replace(",", "", $req['net_total']));
        $approvement->net_total_str = $req['net_total_str'];
        $approvement->cheque = floatval(str_replace(",", "", $req['cheque']));
        $approvement->cheque_str = $req['cheque_str'];
        /** user info */
        $approvement->chg_userid = $req['chg_user'];
        $approvement->chg_date = date("Y-m-d H:i:s");
        
        // $approvement->app_stat = '0';
        // $approvement->is_approve = 'N';
        
        /** Restore debt status to 0 */
        $debts = ApprovementDetail::where('app_id', $appId)->get();
        foreach ($debts as $key => $debt) {
            Debt::find($debt->debt_id)->update(['debt_status' => 0]);
        }

        if($approvement->save()) {
            $index = 0;
            foreach ($req['debts'] as $debt) {
                /** Added Approvement Detail */
                $detail = new ApprovementDetail();
                $detail->app_id = $approvement->app_id;
                $detail->debt_id = $debt['debt_id'];
                $detail->seq_no = ++$index;
                $detail->is_paid = 'N';
                $detail->app_detail_stat = '0';
                $detail->save();

                /** Updated debt status to 1 */
                Debt::find($debt['debt_id'])->update(['debt_status' => 1]);
            }

            return [
                "status"    => "success",
                "message"   => "Update success.",
            ];
        } else {
            return [
                "status" => "error",
                "message" => "Update failed.",
            ];
        }
    }

    public function cancel(Request $req)
    {
        try {
            Approvement::find($req['approveId'])->update(['app_stat' => 3]); /** ยกเลิก */

            $str = '';
            foreach ($req['approveDebts'] as $debt) {
                Debt::find($debt)->update(['debt_status' => 0]);
                $str .= $debt. ',';
            }

            return [
                "status"        => "success",
                "message"       => "Cancel success.",
                "approveDebts"  => $str,
            ];
        } catch (Exception $ex) {
            return [
                "status" => "error",
                "message" => "Cancel failed with $ex",
            ];
        }
    }

    public function detail($appid)
    {   
        $debttypes = [];

        foreach (DebtType::all()->toArray() as $type) {
            $debttypes[$type['debt_type_id']] = $type['debt_type_name'];
        }        

        return [
            'appdetails' => ApprovementDetail::where(['app_id' => $appid])
                                                ->with('debt')
                                                ->orderBy('seq_no', 'ASC')
                                                ->get(),
            'debttypes' => $debttypes,
        ];
    }

    public function supplierApproves($supplier)
    {
        return [
            'approvements' => Approvement::where(['supplier_id' => $supplier])
                                ->with('app_detail')
                                ->where(['app_stat' => 0])
                                ->paginate(5),
        ];
    }
}
