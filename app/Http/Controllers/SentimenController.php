<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\HistoryAnalisis;

class SentimenController extends Controller
{
    public function index()
    {
        return view('app');
    }

    public function analyze(Request $request)
    {
        $request->validate([
            'konten_berita' => 'required|string|min:10',
            'judul'         => 'nullable|string|max:255',
        ]);

        $aiUrl = rtrim(env('AI_API_URL', 'http://localhost:8001'), '/');

        try {
            $aiResponse = Http::timeout(60)
    ->retry(2, 3000)
    ->post("{$aiUrl}/predict", [
        'teks_berita' => $request->konten_berita,
    ]);

            if ($aiResponse->failed()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'AI Service tidak merespons. Coba lagi dalam 30 detik.',
                    'detail'  => $aiResponse->body(),
                ], 503);
            }

            $aiData          = $aiResponse->json();
            $sentimen        = $aiData['sentimen']         ?? 'Netral';
            $confidenceScore = $aiData['confidence_score'] ?? 0;

            HistoryAnalisis::create([
                'judul_berita'     => $request->judul ?? 'Tanpa Judul',
                'konten'           => $request->konten_berita,
                'hasil_sentimen'   => $sentimen,
                'confidence_score' => $confidenceScore,
            ]);

            return response()->json([
                'status'  => 'success',
                'message' => 'Analisis berhasil diproses dan disimpan',
                'data'    => [
                    'judul'    => $request->judul ?? 'Tanpa Judul',
                    'sentimen' => $sentimen,
                    'akurasi'  => $confidenceScore,
                ],
            ], 201);

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Tidak dapat terhubung ke AI Service. Pastikan server ngrok sedang aktif.',
            ], 503);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function historyApi()
    {
        $data = HistoryAnalisis::latest()->take(50)->get();
        return response()->json(['status' => 'success', 'data' => $data]);
    }

    public function stats()
    {
        $total   = HistoryAnalisis::count();
        $positif = HistoryAnalisis::where('hasil_sentimen', 'Positif')->count();
        $negatif = HistoryAnalisis::where('hasil_sentimen', 'Negatif')->count();
        $netral  = HistoryAnalisis::where('hasil_sentimen', 'Netral')->count();

        return response()->json([
            'status' => 'success',
            'data'   => compact('total', 'positif', 'negatif', 'netral'),
        ]);
    }

    public function news()
    {
        $feeds    = [
            'https://www.cnbcindonesia.com/rss',
            'https://www.cnnindonesia.com/ekonomi/rss',
        ];
        $articles = [];

        foreach ($feeds as $feed) {
            try {
                $response = Http::timeout(10)->get($feed);
                if ($response->failed()) continue;
                $xml = simplexml_load_string($response->body());
                if (!$xml) continue;
                $items = $xml->channel->item ?? [];
                foreach (array_slice((array)$items, 0, 4) as $item) {
                    if (!is_object($item)) continue;
                    $title   = (string)($item->title   ?? '');
                    $link    = (string)($item->link    ?? '#');
                    $pubDate = (string)($item->pubDate ?? '');
                    if (empty($title)) continue;
                    $articles[] = [
                        'id'     => count($articles) + 1,
                        'title'  => $title,
                        'source' => str_contains($feed, 'cnbc') ? 'CNBC Indonesia' : 'CNN Indonesia',
                        'time'   => $pubDate ? $this->timeAgo($pubDate) : 'Baru saja',
                        'type'   => 'Netral',
                        'url'    => $link,
                    ];
                }
            } catch (\Exception $e) { continue; }
        }

        if (empty($articles)) $articles = $this->getMockNews();
        return response()->json(['status' => 'success', 'data' => $articles]);
    }

    private function timeAgo(string $datetime): string
    {
        try {
            $diff = time() - strtotime($datetime);
            if ($diff < 3600)       return intval($diff / 60) . ' menit lalu';
            elseif ($diff < 86400)  return intval($diff / 3600) . ' jam lalu';
            elseif ($diff < 604800) return intval($diff / 86400) . ' hari lalu';
            else                    return date('d M Y', strtotime($datetime));
        } catch (\Exception $e) { return 'Baru saja'; }
    }

    private function getMockNews(): array
    {
        return [
            ['id'=>1,'title'=>'IHSG Menguat Didorong Aliran Modal Asing ke Sektor Perbankan','source'=>'Market Watch','time'=>'1 jam lalu','type'=>'Positif','url'=>'https://news.google.com/search?q=IHSG+menguat&hl=id'],
            ['id'=>2,'title'=>'Rupiah Melemah ke Level Terendah Tiga Bulan Terakhir','source'=>'Global Finance','time'=>'3 jam lalu','type'=>'Negatif','url'=>'https://news.google.com/search?q=rupiah+melemah&hl=id'],
            ['id'=>3,'title'=>'Bank Indonesia Pertahankan Suku Bunga Acuan di 6,25%','source'=>'FinTech Journal','time'=>'5 jam lalu','type'=>'Netral','url'=>'https://news.google.com/search?q=BI+suku+bunga&hl=id'],
            ['id'=>4,'title'=>'Fibonacci 61.8% Tahan Laju Koreksi – Analis Optimistis','source'=>'TradingView','time'=>'12 jam lalu','type'=>'Positif','url'=>'https://news.google.com/search?q=fibonacci+koreksi+saham&hl=id'],
            ['id'=>5,'title'=>'Krisis Rantai Pasok Kembali Bayangi Prospek Ekspor Global','source'=>'Economic Times','time'=>'1 hari lalu','type'=>'Negatif','url'=>'https://news.google.com/search?q=krisis+rantai+pasok+ekspor&hl=id'],
            ['id'=>6,'title'=>'Sektor Energi Pimpin Kenaikan Bursa Asia Pagi Ini','source'=>'Investopedia ID','time'=>'1 hari lalu','type'=>'Positif','url'=>'https://news.google.com/search?q=sektor+energi+bursa+asia&hl=id'],
        ];
    }
}
