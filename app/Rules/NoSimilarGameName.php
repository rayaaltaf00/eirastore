<?php

namespace App\Rules;

use App\Models\Game;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NoSimilarGameName implements ValidationRule
{
    protected ?int $ignoreId;

    protected float $similarityThreshold;

    public function __construct(?int $ignoreId = null, float $similarityThreshold = 75.0)
    {
        $this->ignoreId = $ignoreId;
        $this->similarityThreshold = $similarityThreshold;
    }

    /**
     * Jalankan validasi kemiripan nama game.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $inputName = trim((string) $value);
        $normalizedInput = $this->normalizeString($inputName);

        if (empty($normalizedInput)) {
            return;
        }

        $existingGames = Game::query()
            ->when($this->ignoreId, fn ($q) => $q->where('id', '!=', $this->ignoreId))
            ->get(['id', 'nama_game']);

        foreach ($existingGames as $existing) {
            $existingName = trim($existing->nama_game);
            $normalizedExisting = $this->normalizeString($existingName);

            if (empty($normalizedExisting)) {
                continue;
            }

            // 1. Cek identik setelah normalisasi (misal hanya beda spasi atau tanda baca)
            if ($normalizedInput === $normalizedExisting) {
                $fail("Nama game sudah digunakan atau terlalu identik dengan game '{$existingName}'.");

                return;
            }

            // 2. Cek Levenshtein distance untuk typo nama pendek/menengah
            $maxLen = max(strlen($normalizedInput), strlen($normalizedExisting));
            if ($maxLen <= 30) {
                $lev = levenshtein($normalizedInput, $normalizedExisting);
                if ($lev <= 2 && $maxLen > 4) {
                    $fail("Nama game terlalu mirip (terdeteksi kemungkinan salah ketik/typo) dengan game '{$existingName}'.");

                    return;
                }
            }

            // 3. Cek persentase kemiripan teks (similar_text)
            similar_text($normalizedInput, $normalizedExisting, $percent);
            if ($percent >= $this->similarityThreshold) {
                $fail(sprintf("Nama game terlalu mirip dengan game yang sudah ada: '%s' (Tingkat kemiripan %.0f%%).", $existingName, $percent));

                return;
            }

            // 4. Cek awalan / judul utama (misal: "Mobile Legend" vs "Mobile Legends: Bang Bang")
            $shorter = strlen($normalizedInput) < strlen($normalizedExisting) ? $normalizedInput : $normalizedExisting;
            $longer = strlen($normalizedInput) < strlen($normalizedExisting) ? $normalizedExisting : $normalizedInput;

            $shorterStem = rtrim($shorter, 's');
            if (strlen($shorterStem) >= 6 && str_starts_with($longer, $shorterStem)) {
                $fail("Nama game memiliki kemiripan judul utama dengan game '{$existingName}'. Silakan periksa katalog game yang sudah ada.");

                return;
            }
        }
    }

    /**
     * Normalisasi string untuk perbandingan bersih:
     * Menghapus semua karakter non-alfanumerik dan mengubah ke huruf kecil.
     */
    protected function normalizeString(string $str): string
    {
        $lower = mb_strtolower($str, 'UTF-8');

        return preg_replace('/[^a-z0-9]/', '', $lower) ?? '';
    }
}
