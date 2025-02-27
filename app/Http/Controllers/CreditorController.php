<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;

use App\Models\Creditor;
use App\Models\SupplierPrefix;

class CreditorController extends Controller
{
    public function list()
    {
    	return [
            'creditors' => Creditor::all()
        ];
    }

    public function search($searchKey)
    {
        if($searchKey == '0') {
            $creditors = Creditor::paginate(10);
        } else {
            $creditors = Creditor::where('supplier_name', 'like', '%'.$searchKey.'%')->paginate(10);
        }

        return [
            'creditors' => $creditors,
        ];
    }

    private function generateAutoId()
    {
        $supplier = \DB::table('stock_supplier')
                        ->select('supplier_id')
                        ->orderBy('supplier_id', 'DESC')
                        ->first();

        $tmpLastId =  ((int)($supplier->supplier_id)) + 1;
        $lastId = sprintf("%'.05d", $tmpLastId);

        return $lastId;
    }

    public function prefixes()
    {
    	return SupplierPrefix::all();
    }

    public function store(Request $req)
    {
        $lastId = $this->generateAutoId();

        $creditor = new Creditor();

        $creditor->supplier_id = $lastId;
        $creditor->prename_id = $req['prefix'];
        $creditor->supplier_name = $req['supplier_name'];
        $creditor->supplier_payto = $req['supplier_name'];
        $creditor->supplier_address1 = $req['supplier_address1'];
        $creditor->supplier_address2 = $req['supplier_address2'];
        $creditor->supplier_address3 = $req['supplier_address3'];
        $creditor->supplier_zipcode = $req['supplier_zipcode'];
        $creditor->supplier_phone = $req['supplier_phone'];
        $creditor->supplier_fax = $req['supplier_fax'];
        $creditor->supplier_email = $req['supplier_email'];
        $creditor->supplier_taxid = $req['supplier_taxid'];
        $creditor->supplier_back_acc = $req['supplier_back_acc'];
        $creditor->supplier_note = $req['supplier_note'];
        $creditor->supplier_credit = $req['supplier_credit'];
        $creditor->supplier_taxrate = $req['supplier_taxrate'];
        $creditor->supplier_agent_name = $req['supplier_agent_name'];
        $creditor->supplier_agent_contact = $req['supplier_agent_contact'];
        $creditor->supplier_agent_email = $req['supplier_agent_email'];

        if($creditor->save()) {
            return $creditor;
        } else {
            return [
                "status" => "error",
                "message" => "Insert failed.",
            ];
        }
    }

    public function getById($creditorId)
    {
        return [
            'creditor' => Creditor::find($creditorId),
        ];
    }

    public function update(Request $req, $creditorId)
    {
        $creditor = Creditor::find($req['supplier_id']);

        $creditor->prename_id = $req['prefix'];
        $creditor->supplier_name = $req['supplier_name'];
        $creditor->supplier_payto = $req['supplier_name'];
        $creditor->supplier_address1 = $req['supplier_address1'];
        $creditor->supplier_address2 = $req['supplier_address2'];
        $creditor->supplier_address3 = $req['supplier_address3'];
        $creditor->supplier_zipcode = $req['supplier_zipcode'];
        $creditor->supplier_phone = $req['supplier_phone'];
        $creditor->supplier_fax = $req['supplier_fax'];
        $creditor->supplier_email = $req['supplier_email'];
        $creditor->supplier_taxid = $req['supplier_taxid'];
        $creditor->supplier_back_acc = $req['supplier_back_acc'];
        $creditor->supplier_note = $req['supplier_note'];
        $creditor->supplier_credit = $req['supplier_credit'];
        $creditor->supplier_taxrate = $req['supplier_taxrate'];
        $creditor->supplier_agent_name = $req['supplier_agent_name'];
        $creditor->supplier_agent_contact = $req['supplier_agent_contact'];
        $creditor->supplier_agent_email = $req['supplier_agent_email'];

        if($creditor->save()) {
            return $creditor;
        } else {
            return [
                "status" => "error",
                "message" => "Update failed.",
            ];
        }
    }

    public function delete($creditorId)
    {
        $creditor = Creditor::find($creditorId);

        if($creditor->delete()) {
            return $creditor;
        } else {
            return [
                "status" => "error",
                "message" => "Delete failed.",
            ];
        }
    }
}
