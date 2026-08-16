<?php

namespace App\Http\Controllers;

use App\Models\ProductSpecification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SpekController extends Controller
{
    public function index(Request $request)
    {
        $specifications = ProductSpecification::with('creator')
            ->latest('id')
            ->get();

        return view('pages.spek.index', compact('specifications'));
    }

    public function create()
    {
        return view('pages.spek.create', [
            'isEdit' => false,
            'specification' => null,
            'initialFields' => [],
        ]);
    }

    public function edit(ProductSpecification $spek)
    {
        $data = is_array($spek->data) ? $spek->data : [];

        $initialFields = [];

        foreach ($data as $key => $field) {

            if (!is_array($field)) {
                continue;
            }

            $images = [];

            foreach (($field['images'] ?? []) as $image) {

                if (!is_array($image)) {
                    continue;
                }

                /*
                 * Support semua format data lama:
                 * - path
                 * - existing_path
                 * - url
                 * - data yang berupa URL /storage/...
                 */
                $path = $this->resolveImagePath($image);

                $images[] = [
                    'name' => $image['name'] ?? ($path ? basename($path) : 'image'),
                    'type' => $image['type'] ?? null,
                    'size' => $image['size'] ?? null,

                    /*
                     * Jangan jadikan URL sebagai "data" base64.
                     * Preview akan dibuat lewat route image.
                     */
                    'url' => $path
                        ? $this->imageUrl($path)
                        : ($this->isDataImage($image['data'] ?? null)
                            ? $image['data']
                            : ''),

                    'existing_path' => $path,
                ];
            }

            $initialFields[] = [
                'id' => 'existing_' . $key . '_' . $spek->id,
                'label' => $field['label'] ?? $key,
                'type' => $field['type'] ?? 'text',
                'value' => $field['value'] ?? '',
                'unit' => $field['unit'] ?? '',
                'required' => (bool) ($field['required'] ?? false),
                'options' => is_array($field['options'] ?? null)
                    ? array_values($field['options'])
                    : [],
                'images' => $images,
            ];
        }

        return view('pages.spek.create', [
            'isEdit' => true,
            'specification' => $spek,
            'initialFields' => $initialFields,
        ]);
    }

    /**
     * Menampilkan file attachment langsung dari storage/app/public.
     *
     * Ini membuat preview tetap bekerja walaupun symbolic link
     * public/storage belum tersedia di hosting.
     */
    public function image(string $path)
    {
        $path = trim($path, '/');

        if (
            $path === '' ||
            str_contains($path, '..') ||
            str_contains($path, '\\')
        ) {
            abort(404);
        }

        $disk = Storage::disk('public');

        if (!$disk->exists($path)) {
            abort(404);
        }

        $fullPath = $disk->path($path);

        return response()->file($fullPath, [
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'article_code' => [
                'required',
                'string',
                'max:100',
                'unique:product_specifications,article_code',
            ],
            'specifications' => ['required', 'json'],
        ]);

        $fields = json_decode($validated['specifications'], true);

        if (!is_array($fields)) {
            throw ValidationException::withMessages([
                'specifications' => 'Format spesifikasi tidak valid.',
            ]);
        }

        $data = $this->buildData(
            $fields,
            $validated['article_code']
        );

        $specification = ProductSpecification::create([
            'name' => $validated['name'],
            'article_code' => $validated['article_code'],
            'data' => $data,
            'created_by' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Spesifikasi berhasil disimpan.',
            'redirect' => route('spek.index'),
            'id' => $specification->id,
        ]);
    }

    public function update(
        Request $request,
        ProductSpecification $spek
    ) {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],

            'article_code' => [
                'required',
                'string',
                'max:100',
                Rule::unique(
                    'product_specifications',
                    'article_code'
                )->ignore($spek->id),
            ],

            'specifications' => ['required', 'json'],
        ]);

        $fields = json_decode(
            $validated['specifications'],
            true
        );

        if (!is_array($fields)) {
            throw ValidationException::withMessages([
                'specifications' =>
                    'Format spesifikasi tidak valid.',
            ]);
        }

        /*
         * Ambil semua path gambar lama.
         */
        $oldPaths = $this->collectImagePaths(
            is_array($spek->data)
                ? $spek->data
                : []
        );

        /*
         * Ambil path gambar lama yang MASIH dipakai dari request.
         *
         * INI BAGIAN YANG SEBELUMNYA BUG.
         * Sebelumnya $keepPaths tidak pernah diisi sehingga
         * semua gambar lama dianggap sudah dihapus.
         */
        $keepPaths = $this->collectExistingPathsFromFields(
            $fields
        );

        /*
         * Build data baru.
         *
         * Gambar existing_path tidak di-upload ulang.
         * Gambar baru (data:image/...) disimpan ke storage.
         */
        $newData = $this->buildData(
            $fields,
            $validated['article_code']
        );

        /*
         * Setelah data baru berhasil dibuat, hapus file lama
         * yang benar-benar sudah dihapus dari form.
         */
        $deletePaths = array_values(
            array_diff(
                $oldPaths,
                $keepPaths
            )
        );

        DB::transaction(function () use (
            $spek,
            $validated,
            $newData,
            $deletePaths
        ) {

            $spek->update([
                'name' => $validated['name'],
                'article_code' => $validated['article_code'],
                'data' => $newData,
            ]);

            foreach ($deletePaths as $path) {

                if (
                    $path &&
                    Storage::disk('public')->exists($path)
                ) {
                    Storage::disk('public')->delete($path);
                }
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Spesifikasi berhasil diperbarui.',
            'redirect' => route('spek.index'),
            'id' => $spek->id,
        ]);
    }

    private function buildData(
        array $fields,
        string $articleCode
    ): array {

        $data = [];

        foreach ($fields as $field) {

            if (!is_array($field)) {
                continue;
            }

            $label = trim(
                (string) ($field['label'] ?? '')
            );

            if ($label === '') {
                continue;
            }

            $key = $this->makeKey($label);
            $baseKey = $key;
            $counter = 2;

            while (array_key_exists($key, $data)) {
                $key = $baseKey . '_' . $counter++;
            }

            $images = [];

            foreach (($field['images'] ?? []) as $image) {

                if (!is_array($image)) {
                    continue;
                }

                /*
                 * IMAGE LAMA
                 */
                $existingPath =
                    $this->resolveImagePath($image);

                if ($existingPath) {

                    /*
                     * Kalau file lama sudah tidak ada,
                     * jangan membuat record palsu yang nantinya
                     * tampil sebagai broken image.
                     */
                    if (
                        !Storage::disk('public')
                            ->exists($existingPath)
                    ) {
                        continue;
                    }

                    $images[] = [
                        'name' =>
                            $image['name']
                            ?? basename($existingPath),

                        'type' =>
                            $image['type']
                            ?? null,

                        'size' =>
                            $image['size']
                            ?? null,

                        'path' =>
                            $existingPath,

                        'url' =>
                            $this->imageUrl(
                                $existingPath
                            ),
                    ];

                    continue;
                }

                /*
                 * IMAGE BARU
                 */
                $dataUrl =
                    $image['data']
                    ?? null;

                if (
                    !$this->isDataImage($dataUrl)
                ) {
                    continue;
                }

                $stored =
                    $this->storeDataUrlImage(
                        $dataUrl,
                        $articleCode
                    );

                if (!$stored) {
                    continue;
                }

                $images[] = [
                    'name' =>
                        $image['name']
                        ?? 'image',

                    'type' =>
                        $image['type']
                        ?? null,

                    'size' =>
                        $image['size']
                        ?? null,

                    'path' =>
                        $stored,

                    'url' =>
                        $this->imageUrl(
                            $stored
                        ),
                ];
            }

            $item = [
                'label' =>
                    $label,

                'type' =>
                    $field['type']
                    ?? 'text',

                'value' =>
                    $field['value']
                    ?? '',

                'unit' =>
                    $field['unit']
                    ?? null,

                'required' =>
                    (bool) (
                        $field['required']
                        ?? false
                    ),

                'images' =>
                    $images,
            ];

            if (
                ($field['type'] ?? '') ===
                'select'
            ) {
                $item['options'] =
                    array_values(
                        array_filter(
                            is_array(
                                $field['options']
                                ?? null
                            )
                                ? $field['options']
                                : []
                        )
                    );
            }

            $data[$key] = $item;
        }

        return $data;
    }

    private function collectExistingPathsFromFields(
        array $fields
    ): array {

        $paths = [];

        foreach ($fields as $field) {

            if (!is_array($field)) {
                continue;
            }

            foreach (($field['images'] ?? []) as $image) {

                if (!is_array($image)) {
                    continue;
                }

                /*
                 * Untuk update, hanya existing_path yang dianggap
                 * sebagai file lama yang harus dipertahankan.
                 */
                $path =
                    $image['existing_path']
                    ?? null;

                if (!$path) {
                    continue;
                }

                $path =
                    $this->normalizeStoragePath(
                        $path
                    );

                if ($path) {
                    $paths[] = $path;
                }
            }
        }

        return array_values(
            array_unique($paths)
        );
    }

    private function collectImagePaths(
        array $data
    ): array {

        $paths = [];

        foreach ($data as $field) {

            if (!is_array($field)) {
                continue;
            }

            foreach (($field['images'] ?? []) as $image) {

                if (!is_array($image)) {
                    continue;
                }

                $path =
                    $this->resolveImagePath(
                        $image
                    );

                if ($path) {
                    $paths[] = $path;
                }
            }
        }

        return array_values(
            array_unique($paths)
        );
    }

    /**
     * Ambil storage path dari berbagai format lama.
     */
    private function resolveImagePath(
        array $image
    ): ?string {

        $candidates = [
            $image['existing_path'] ?? null,
            $image['path'] ?? null,
            $image['url'] ?? null,
            $image['data'] ?? null,
        ];

        foreach ($candidates as $value) {

            if (
                !is_string($value) ||
                trim($value) === ''
            ) {
                continue;
            }

            if ($this->isDataImage($value)) {
                continue;
            }

            $path =
                $this->normalizeStoragePath(
                    $value
                );

            if ($path) {
                return $path;
            }
        }

        return null;
    }

    /**
     * Normalisasi:
     * /storage/product-specifications/a.jpg
     * https://domain.com/storage/product-specifications/a.jpg
     * product-specifications/a.jpg
     *
     * menjadi:
     * product-specifications/a.jpg
     */
    private function normalizeStoragePath(
        string $value
    ): ?string {

        $value = trim($value);

        if ($value === '') {
            return null;
        }

        /*
         * Full URL.
         */
        if (filter_var($value, FILTER_VALIDATE_URL)) {

            $parsedPath =
                parse_url(
                    $value,
                    PHP_URL_PATH
                );

            if (!$parsedPath) {
                return null;
            }

            $value = $parsedPath;
        }

        $value =
            urldecode($value);

        /*
         * Buang query string / fragment.
         */
        $value =
            preg_replace(
                '/[?#].*$/',
                '',
                $value
            );

        $value =
            ltrim($value, '/');

        /*
         * storage/xxx -> xxx
         */
        if (
            str_starts_with(
                $value,
                'storage/'
            )
        ) {
            $value =
                substr(
                    $value,
                    strlen('storage/')
                );
        }

        /*
         * public/storage/xxx -> xxx
         */
        if (
            str_starts_with(
                $value,
                'public/storage/'
            )
        ) {
            $value =
                substr(
                    $value,
                    strlen('public/storage/')
                );
        }

        if (
            $value === '' ||
            str_contains($value, '..') ||
            str_contains($value, '\\')
        ) {
            return null;
        }

        return $value;
    }

    private function imageUrl(
        string $path
    ): string {

        /*
         * Lewat controller route, jadi tidak bergantung
         * pada public/storage symlink.
         */
        return url(
            '/spek/image/' .
            implode(
                '/',
                array_map(
                    'rawurlencode',
                    explode('/', ltrim($path, '/'))
                )
            )
        );
    }

    private function isDataImage(
        mixed $value
    ): bool {

        return is_string($value)
            && str_starts_with(
                $value,
                'data:image/'
            );
    }

    private function makeKey(
        string $label
    ): string {

        $key =
            strtolower(
                trim($label)
            );

        $key =
            preg_replace(
                '/[^\pL\pN]+/u',
                '_',
                $key
            );

        return
            trim($key, '_')
            ?: 'field';
    }

    private function storeDataUrlImage(
        string $dataUrl,
        string $articleCode
    ): ?string {

        if (!preg_match(
            '/^data:image\/(jpeg|jpg|png|webp);base64,(.+)$/',
            $dataUrl,
            $matches
        )) {
            return null;
        }

        $extension =
            $matches[1] === 'jpeg'
                ? 'jpg'
                : $matches[1];

        $binary =
            base64_decode(
                $matches[2],
                true
            );

        if ($binary === false) {
            return null;
        }

        /*
         * Batas sederhana supaya request tidak menyimpan
         * data image yang terlalu besar.
         */
        if (strlen($binary) > 5 * 1024 * 1024) {
            return null;
        }

        $safeCode =
            preg_replace(
                '/[^A-Za-z0-9_-]/',
                '_',
                $articleCode
            );

        $filename =
            uniqid(
                'spec_',
                true
            ) .
            '.' .
            $extension;

        $path =
            'product-specifications/' .
            $safeCode .
            '/' .
            $filename;

        Storage::disk('public')
            ->put($path, $binary);

        return $path;
    }
}