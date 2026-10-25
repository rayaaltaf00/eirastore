<?php

namespace App\Http\Controllers;

use App\Models\Game;
use Illuminate\View\View;

class GameCatalogController extends Controller
{
    /**
     * Tampilkan detail game dan daftar layanan joki yang tersedia.
     * Referensi: C-05, C-06
     */
    public function show(Game $game): View
    {
        // Hanya game aktif yang dapat diakses publik
        abort_if(!$game->aktif, 404);

        $layananList = $game->layanan()
            ->aktif()
            ->with(['items' => function ($query) {
                $query->aktif()->orderBy('urutan');
            }])
            ->get();

        return view('game.show', compact('game', 'layananList'));
    }
}
