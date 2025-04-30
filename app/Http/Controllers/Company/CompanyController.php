<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Company;
use Inertia\Inertia;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class CompanyController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $rows = $request->input('rows', 10);
        $page = $request->input('page', 1);
        $search = $request->input('search', '');

        $companies = Company::where('user_id', Auth::id())
            ->where('name', 'like', "%{$search}%")
            ->orderByRaw('LOWER(name) ASC')
            ->paginate($rows, ['*'], 'page', $page);

        return Inertia::render('company/index', [
            'companies' => $companies->items(),
            'pagination' => [
                'total' => $companies->total(),
                'currentPage' => $companies->currentPage(),
                'lastPage' => $companies->lastPage(),
                'perPage' => $companies->perPage()
            ],
            'search' => $request->input('search')
        ]);

    }

    public function search(Request $request){
        $search = $request->input('search','');
        $companies = Auth::user()
            ->companies()
            ->where('name','like',"%{$search}%")
            ->select('id','name')
            ->orderByRaw('LOWER(name) ASC')
            ->limit(10)
            ->get();

        return response()->json($companies);
    }

    public function store(Request $request){
        $request->validate([
            'name'         => 'required|string|max:30',
            'email'        => 'nullable|email|max:20',
            'phone_number' => 'nullable|regex:/^[0-9+\-\s]+$/|max:20',
            'website'      => 'nullable|url|max:20',
        ]);

        Company::create([
            'name'=>$request->name,
            'user_id'=>auth()->id(),
            'email'=>$request->email,
            'phone_number'=>$request->phone_number,
            'website'=>$request->website
        ]);

        return redirect()->back()->with('success','Company created successfully.');
    }

    public function update(Request $request, Company $company){
        $this->authorize('update', $company);

        $request->validate([
            'name'         => 'required|string|max:30',
            'email'        => 'nullable|email|max:20',
            'phone_number' => 'nullable|regex:/^[0-9+\-\s]+$/|max:20',
            'website'      => 'nullable|url|max:20',
        ]);

        $company->update([
            'name'=>$request->name,
            'email'=>$request->email,
            'phone_number'=>$request->phone_number,
            'website'=>$request->website
        ]);

        return redirect()->back()->with('success', 'Company updated successfully.');

    }

    public function destroy(Company $company)
    {
        $this->authorize('delete', $company);
        $company->delete();
        return redirect()->back()->with('success', 'Company deleted successfully.');
    }
}
