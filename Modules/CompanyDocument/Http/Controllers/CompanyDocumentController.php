<?php
namespace Modules\CompanyDocument\Http\Controllers;

use App\Services\FirebaseService;
use App\Traits\File;
use Exception;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Modules\CompanyDocument\Entities\CompanyDocument;
use Yajra\DataTables\Facades\DataTables;

class CompanyDocumentController extends Controller
{
    use File;
    protected $fcmService;

    public function __construct(FirebaseService $fcmService)
    {
        view()->share('activeLink', 'companydocument');
        $this->fcmService = $fcmService;
    }

    /**
     * Display a listing of the companydocument.
     * @return Renderable
     */
    public function index(Request $request)
    {

        canPerform('Manage CompanyDocument');
        if ($request->ajax()) {
            $data = CompanyDocument::get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = '';
                    $btn = createActionButton(route('backend.companydocument.show', $row), 'View', 'btn-success', 'fa fa-eye');
                    if (hasPermission('Edit CompanyDocument')) {
                        $btn .= createActionButton(route('backend.companydocument.edit', $row), 'Edit', 'btn-warning edit-button', 'fa fa-edit');
                    }
                    if (hasPermission('Delete CompanyDocument')) {
                        $btn .= createActionButton(route('backend.companydocument.destroy', $row), 'Delete', 'btn-danger action-button', 'fa fa-trash', 'datatable');
                    }
                    return $btn;
                })
                ->rawColumns(['action', 'status'])
                ->make(true);
        }
        return view('companydocument::index');
    }

    /**
     * show  the lisitng companydocument from storage.
     */
    public function show(CompanyDocument $companydocument)
    {
        // dd($companydocument->creator);
        return view('companydocument::.show', compact('companydocument'));
    }

    public function create()
    {
        canPerform('Create CompanyDocument');
        $html = view('companydocument::.create')->render();
        // dd($html);
        return response()->json([
            'success' => true,
            'html'    => $html,
        ]);
    }

    public function store(Request $request)
    {
        canPerform('Create CompanyDocument');

        $data = $request->validate([
            'legal_trade_name'   => 'required|string|max:255',
            'short_name'         => 'required|string|max:255',
            'license_number'     => 'required|string|max:255',
            'license_expiry'     => 'required|date',
            'added_date'         => 'required|date',
            'mol_code'           => 'required|string|max:255',
            'document'           => 'nullable|file|max:20480',
            'employer_reference' => 'required|string|max:255',
            'routing_number'     => 'required|string|max:255',
            'logo'               => 'nullable|file|image|max:2048',
            'small_logo'         => 'nullable|file|image|max:2048',
            'sign'               => 'nullable|file|image|max:2048',
            'header'             => 'nullable|file|image|max:2048',
            'footer'             => 'nullable|file|image|max:2048',

        ]);

        $response = getErrorResponse();
        try {

            if (isset($request->document)) {
                $file     = $request->document;
                $fileName = time() . '.document.' . $request->document->extension();

                $type     = $request->document->getClientMimeType();
                $size     = $request->document->getSize();
                $location = "uploads/companydocument/";

                $storagePath = public_path($location);

                $ret              = $request->document->move($location, $fileName);
                $data['document'] = $fileName;
            }
            if (isset($request->logo)) {
                $file     = $request->logo;
                $fileName = time() . '.logo.' . $request->logo->extension();

                $type     = $request->logo->getClientMimeType();
                $size     = $request->logo->getSize();
                $location = "uploads/companydocument/";

                $storagePath = public_path($location);

                $ret          = $request->logo->move($location, $fileName);
                $data['logo'] = $fileName;
            }
            if (isset($request->small_logo)) {
                $file     = $request->small_logo;
                $fileName = time() . '.small_logo.' . $request->small_logo->extension();

                $type     = $request->small_logo->getClientMimeType();
                $size     = $request->small_logo->getSize();
                $location = "uploads/companydocument/";

                $storagePath = public_path($location);

                $ret                = $request->small_logo->move($location, $fileName);
                $data['small_logo'] = $fileName;
            }
            if (isset($request->sign)) {
                $file     = $request->sign;
                $fileName = time() . '.sign.' . $request->sign->extension();

                $type     = $request->sign->getClientMimeType();
                $size     = $request->sign->getSize();
                $location = "uploads/companydocument/";

                $storagePath = public_path($location);

                $ret          = $request->sign->move($location, $fileName);
                $data['sign'] = $fileName;
            }
            if (isset($request->header)) {
                $file     = $request->header;
                $fileName = time() . '.header.' . $request->header->extension();

                $type     = $request->header->getClientMimeType();
                $size     = $request->header->getSize();
                $location = "uploads/companydocument/";

                $storagePath = public_path($location);

                $ret            = $request->header->move($location, $fileName);
                $data['header'] = $fileName;
            }
            if (isset($request->footer)) {
                $file     = $request->footer;
                $fileName = time() . '.footer.' . $request->footer->extension();

                $type     = $request->footer->getClientMimeType();
                $size     = $request->footer->getSize();
                $location = "uploads/companydocument/";

                $storagePath = public_path($location);

                $ret            = $request->footer->move($location, $fileName);
                $data['footer'] = $fileName;
            }

            $companydocument = CompanyDocument::create($data);

            $response = getSuccessResponse(createFlashMessage('Company Document', 'created'));
        } catch (Exception $e) {
            dd($e->getMessage());
            $response['error'] = $e->getMessage();
        }

        return response()->json($response);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CompanyDocument $companydocument)
    {
        canPerform('Edit CompanyDocument');

        $html = view('companydocument::.edit', compact('companydocument'))->render();

        return response()->json([
            'success' => true,
            'html'    => $html,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        canPerform('Create CompanyDocument');
        $companydocument = CompanyDocument::findOrFail($id);
        $data            = $request->validate([
            'legal_trade_name'   => 'required|string|max:255',
            'short_name'         => 'required|string|max:255',
            'license_number'     => 'required|string|max:255',
            'license_expiry'     => 'required|date',
            'added_date'         => 'required|date',
            'mol_code'           => 'required|string|max:255',
            'document'           => 'nullable|file|max:20480',
            'employer_reference' => 'required|string|max:255',
            'routing_number'     => 'required|string|max:255',

        ]);

        $response = getErrorResponse();
        try {
            if (isset($request->document)) {
                $file             = $request->document;
                $fileName         = time() . '.document.' . $request->document->extension();
                $type             = $request->document->getClientMimeType();
                $size             = $request->document->getSize();
                $location         = "uploads/companydocument/";
                $storagePath      = public_path($location);
                $ret              = $request->document->move($location, $fileName);
                $data['document'] = $fileName;
            }
            if (isset($request->logo)) {
                $file         = $request->logo;
                $fileName     = time() . '.logo.' . $request->logo->extension();
                $type         = $request->logo->getClientMimeType();
                $size         = $request->logo->getSize();
                $location     = "uploads/companydocument/";
                $storagePath  = public_path($location);
                $ret          = $request->logo->move($location, $fileName);
                $data['logo'] = $fileName;
            }
            if (isset($request->small_logo)) {
                $file               = $request->small_logo;
                $fileName           = time() . '.small_logo.' . $request->small_logo->extension();
                $type               = $request->small_logo->getClientMimeType();
                $size               = $request->small_logo->getSize();
                $location           = "uploads/companydocument/";
                $storagePath        = public_path($location);
                $ret                = $request->small_logo->move($location, $fileName);
                $data['small_logo'] = $fileName;
            }
            if (isset($request->sign)) {
                $file         = $request->sign;
                $fileName     = time() . '.sign.' . $request->sign->extension();
                $type         = $request->sign->getClientMimeType();
                $size         = $request->sign->getSize();
                $location     = "uploads/companydocument/";
                $storagePath  = public_path($location);
                $ret          = $request->sign->move($location, $fileName);
                $data['sign'] = $fileName;
            }
            if (isset($request->header)) {
                $file           = $request->header;
                $fileName       = time() . '.header.' . $request->header->extension();
                $type           = $request->header->getClientMimeType();
                $size           = $request->header->getSize();
                $location       = "uploads/companydocument/";
                $storagePath    = public_path($location);
                $ret            = $request->header->move($location, $fileName);
                $data['header'] = $fileName;
            }
            if (isset($request->footer)) {
                $file           = $request->footer;
                $fileName       = time() . '.footer.' . $request->footer->extension();
                $type           = $request->footer->getClientMimeType();
                $size           = $request->footer->getSize();
                $location       = "uploads/companydocument/";
                $storagePath    = public_path($location);
                $ret            = $request->footer->move($location, $fileName);
                $data['footer'] = $fileName;
            }
            $companydocument->update($data);

            $response = getSuccessResponse(createFlashMessage('Company Document Request', 'updated'));
        } catch (Exception $e) {
            $response['message'] = $e->getMessage();
        }
        return response()->json($response);
    }

    /**
     * Remove the specified companydocument from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy(CompanyDocument $companydocument)
    {
        $companydocument->delete();

        $response = getSuccessResponse(createFlashMessage('Company Document Request', 'deleted'));
        // } else {
        // $response['message'] = __trans('permission_denied');
        // }

        return response()->json($response);
    }
}
