<?php

namespace App\Http\Controllers\Back;

use App\Http\Controllers\Controller;
use App\Http\Resources\Datatable\Catalogs\CatalogCollection;
use App\Models\Catalog;
use App\Models\Link;
use App\Models\Menu;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PageController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Page::class, 'page');
    }

    public function index()
    {
        $pages = Page::detectLang()->latest()->paginate(10);

        return view('back.pages.index', compact('pages'));
    }

    public function create()
    {
        return view('back.pages.create');
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'title' => 'required|string|max:191',
            'content' => 'required',
            'slug' => 'nullable|unique:pages,slug'
        ]);

        Page::create([
            'title' => $request->title,
            'content' => $request->content,
            'slug' => $request->slug ?: $request->title,
            'published' => $request->published ? true : false,
            'lang' => app()->getLocale(),
        ]);

        toastr()->success('صفحه با موفقیت ایجاد شد.');

        return response("success", 200);
    }

    public function edit(Page $page)
    {
        return view('back.pages.edit', compact('page'));
    }

    public function update(Request $request, Page $page)
    {
        $this->validate($request, [
            'title' => 'required|string|max:191',
            'content' => 'required',
        ]);

        $slug = $page->slug;

        $page->update([
            'title' => $request->title,
            'content' => $request->content,
            'slug' => $request->slug ?: $request->title,
            'published' => $request->published ? true : false,
        ]);

        Menu::where('link', '/pages/' . $slug)->update([
            'link' => '/pages/' . $page->slug,
        ]);

        Link::where('link', '/pages/' . $slug)->update([
            'link' => '/pages/' . $page->slug,
        ]);

        toastr()->success('صفحه با موفقیت ویرایش شد.');

        return response("success", 200);
    }

    public function destroy(Page $page)
    {
        $page->delete();

        return response("success", 200);
    }

    public function catalogs()
    {
        return view('back.pages.catalogs');
    }

    public function apiIndex(Request $request)
    {
        $catalogs = Catalog::filter($request);
        $catalogs = datatable($request, $catalogs);
        return new CatalogCollection($catalogs);
    }

    public function editCatalog(Catalog $catalog)
    {
        return view('back.pages.editCatalog', compact('catalog'));
    }

    public function updateCatalog(Request $request, Catalog $catalog)
    {

        $this->validate($request, [
            'title' => 'required|string|max:191',
        ]);


        $slug = $catalog->slug;

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('uploads/catalog', 'public');
        }

        $catalog->update([
            'title' => $request->title,
            'description' => $request->meta_description,
            'meta_description' => $request->meta_description,
            'link' => $request->link,
            'slug' => $request->slug ?: $request->title,
            'published' => $request->published ? true : false,
            'image' => $imagePath,
        ]);

        Menu::where('link', '/pages/' . $slug)->update([
            'link' => '/pages/' . $catalog->slug,
        ]);

        Link::where('link', '/pages/' . $slug)->update([
            'link' => '/pages/' . $catalog->slug,
        ]);

        toastr()->success('صفحه با موفقیت ویرایش شد.');

        return view('back.pages.catalogs', compact('catalog'));
    }

    public function destroyCatalog(Catalog $catalog)
    {
        dd($catalog);
        $catalog->delete();
        toastr()->success('کاتالوگ با موفقت حذف شد');
        return view('back.pages.catalogs');
    }

    public function createCatalog()
    {
        return view('back.pages.createCatalog');
    }

    public function storeCatalog(Request $request)
    {
        DB::beginTransaction();
        try {
            $this->validate($request, [
                'title' => 'required|string|max:191',
                'slug' => 'nullable|unique:catalogs,slug',
                'image' => 'nullable',
            ]);

            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('uploads/catalog', 'public');
            }

            Catalog::create([
                'title' => $request->title,
                'description' => $request->meta_description,
                'meta_description' => $request->meta_description,
                'link' => $request->link,
                'slug' => $request->slug ?: $request->title,
                'published' => $request->published ? true : false,
                'lang' => app()->getLocale(),
                'image' => $imagePath,
            ]);

            DB::commit();
            toastr()->success('کاتالوگ با موفقیت ایجاد شد.');
            return view('back.pages.catalogs');
        } catch (\Exception $e) {
            DB::rollBack();
            toastr()->error('خطایی در ایجاد کاتالوگ رخ داد.');
            return redirect()->back()->withErrors($e->getMessage());
        }
    }
}
