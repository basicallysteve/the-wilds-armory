<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Services\MonsterHunterWildsDatabase;
use Illuminate\Http\Request;
use App\Traits\Filter;
use Inertia\Inertia;

class ArmorController extends Controller
{
    use Filter;
    
    private $mhwdb;
    public function __construct()
    {
        $this->mhwdb = resolve(MonsterHunterWildsDatabase::class);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {   
        $filters = $this->parseFilters($request->all());
        return $this->mhwdb->limit((float)($request->input('limit') ?? 20))
            ->offset((float)($request->input('offset') ?? 0))->getArmors($filters);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $mhwdb = resolve(MonsterHunterWildsDatabase::class);
        $armor = $mhwdb->getArmor($id);
        if (!$armor) {
            return response()->json(['message' => 'Armor not found'], 404);
        }
        return response()->json($armor);
    }

    public function armory(Request $request)
    {
        $filters = $this->parseFilters(request()->all());
        return Inertia::render('Armory/Index', [
            'armors' => $this->mhwdb->limit((float)($request->input('limit') ?? 20))
            ->offset((float)($request->input('offset') ?? 0))->getArmors($filters),
        ]);
    }
}