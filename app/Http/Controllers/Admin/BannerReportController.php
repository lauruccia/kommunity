<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BannerCampaign;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BannerReportController extends Controller
{
    public function export(BannerCampaign $bannerCampaign): StreamedResponse
    {
        $bannerCampaign->load(['advertiser', 'placements', 'chapters', 'regions', 'cities', 'professions', 'categories']);

        return response()->streamDownload(function () use ($bannerCampaign): void {
            $out = fopen('php://output', 'w');

            fputcsv($out, ['Campagna', $bannerCampaign->name]);
            fputcsv($out, ['Inserzionista', $bannerCampaign->advertiser?->name ?? '-']);
            fputcsv($out, ['Pacchetto', $bannerCampaign->sales_package]);
            fputcsv($out, ['Periodo', optional($bannerCampaign->starts_at)->format('Y-m-d H:i') . ' / ' . optional($bannerCampaign->ends_at)->format('Y-m-d H:i')]);
            fputcsv($out, ['Target', $bannerCampaign->targetSummary()]);
            fputcsv($out, []);

            $impressions = $bannerCampaign->impressions()->count();
            $clicks = $bannerCampaign->clicks()->count();
            $ctr = $impressions > 0 ? round(($clicks / $impressions) * 100, 2) : 0;

            fputcsv($out, ['Metriche']);
            fputcsv($out, ['Impression', 'Click', 'CTR %']);
            fputcsv($out, [$impressions, $clicks, $ctr]);
            fputcsv($out, []);

            fputcsv($out, ['Dettaglio giornaliero']);
            fputcsv($out, ['Data', 'Placement', 'Impression', 'Click', 'CTR %']);

            $impressionsRows = $bannerCampaign->impressions()
                ->selectRaw('DATE(shown_at) as day, placement_key, COUNT(*) as total')
                ->groupByRaw('DATE(shown_at), placement_key')
                ->get();

            $clickRows = $bannerCampaign->clicks()
                ->selectRaw('DATE(clicked_at) as day, placement_key, COUNT(*) as total')
                ->groupByRaw('DATE(clicked_at), placement_key')
                ->get()
                ->keyBy(fn ($row) => $row->day . '|' . $row->placement_key);

            foreach ($impressionsRows as $row) {
                $key = $row->day . '|' . $row->placement_key;
                $rowClicks = (int) ($clickRows[$key]->total ?? 0);
                $rowImpressions = (int) $row->total;
                fputcsv($out, [
                    $row->day,
                    $row->placement_key,
                    $rowImpressions,
                    $rowClicks,
                    $rowImpressions > 0 ? round(($rowClicks / $rowImpressions) * 100, 2) : 0,
                ]);
            }

            fclose($out);
        }, 'banner-campaign-' . $bannerCampaign->id . '-report.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
