<?php

namespace App\Services;

class InsightEngine
{
    /**
     * Forecast next K points for a numeric series using simple least-squares regression.
     * @param array $series Numeric array [y1, y2, ...] ordered oldest -> newest
     * @param int   $periodsAhead Number of points to forecast
     * @return float[]
     */
    public function forecastLinear(array $series, int $periodsAhead = 1): array
    {
        $n = count($series);
        if ($n === 0) return array_fill(0, $periodsAhead, 0.0);
        if ($n < 3) {
            // Not enough history — assume last value continues
            $last = (float) end($series);
            return array_fill(0, $periodsAhead, $last);
        }

        $xs = range(1, $n);
        $ys = array_map('floatval', $series);

        $sumX = array_sum($xs);
        $sumY = array_sum($ys);
        $sumXX = 0.0; $sumXY = 0.0;
        for ($i = 0; $i < $n; $i++) {
            $sumXX += $xs[$i] * $xs[$i];
            $sumXY += $xs[$i] * $ys[$i];
        }
        $den = ($n * $sumXX - $sumX * $sumX) ?: 1e-9;
        $m = ($n * $sumXY - $sumX * $sumY) / $den;         // slope
        $b = ($sumY - $m * $sumX) / $n;                    // intercept

        $out = [];
        for ($k = 1; $k <= $periodsAhead; $k++) {
            $x = $n + $k;
            $yhat = $m * $x + $b;
            $out[] = max(0, round($yhat, 2));
        }
        return $out;
    }


    /**
 * Return slope, intercept, and R^2 for a simple linear regression on $series.
 * @return array{m: float, b: float, r2: float}
 */
public function regress(array $series): array
{
    $n = count($series);
    if ($n < 3) {
        $last = $n ? (float) end($series) : 0.0;
        return ['m' => 0.0, 'b' => $last, 'r2' => 0.0];
    }

    $xs = range(1, $n);
    $ys = array_map('floatval', $series);

    $sumX = array_sum($xs);
    $sumY = array_sum($ys);
    $sumXX = 0.0; $sumXY = 0.0; $sumYY = 0.0;
    for ($i = 0; $i < $n; $i++) {
        $sumXX += $xs[$i] * $xs[$i];
        $sumXY += $xs[$i] * $ys[$i];
        $sumYY += $ys[$i] * $ys[$i];
    }
    $den = ($n * $sumXX - $sumX * $sumX) ?: 1e-9;
    $m = ($n * $sumXY - $sumX * $sumY) / $den;
    $b = ($sumY - $m * $sumX) / $n;

    // R^2
    $yMean = $sumY / $n;
    $ssTot = 0.0; $ssRes = 0.0;
    for ($i = 0; $i < $n; $i++) {
        $yHat = $m * $xs[$i] + $b;
        $ssTot += ($ys[$i] - $yMean) ** 2;
        $ssRes += ($ys[$i] - $yHat) ** 2;
    }
    $r2 = $ssTot > 0 ? max(0, 1 - ($ssRes / $ssTot)) : 0.0;

    return ['m' => $m, 'b' => $b, 'r2' => $r2];
}

/** Forecast next K and return ['values' => [...], 'r2' => float] */
public function forecastWithR2(array $series, int $periodsAhead = 1): array
{
    $fit = $this->regress($series);
    $n = count($series);
    if ($n === 0) {
        return ['values' => array_fill(0, $periodsAhead, 0.0), 'r2' => 0.0];
    }
    $out = [];
    for ($k = 1; $k <= $periodsAhead; $k++) {
        $x = $n + $k;
        $yhat = $fit['m'] * $x + $fit['b'];
        $out[] = max(0, round($yhat, 2));
    }
    return ['values' => $out, 'r2' => $fit['r2']];
}


    /**
     * Classify direction between last actual and first forecast using a tolerance band.
     * @param float $lastActual
     * @param float $forecast
     * @param float $bandPct e.g. 0.10 = ±10% is "stable"
     */
    public function classifyTrend(float $lastActual, float $forecast, float $bandPct = 0.10): string
    {
        if ($lastActual <= 0) return $forecast > 0 ? 'increasing' : 'stable';
        $delta = ($forecast - $lastActual) / $lastActual;
        if ($delta >  $bandPct) return 'increasing';
        if ($delta < -$bandPct) return 'decreasing';
        return 'stable';
    }

    /** Bucket a percentage into strong/moderate/low (for conversion status). */
    public function rateBucket(float $pct): string
    {
        return $pct >= 50 ? 'strong' : ($pct >= 25 ? 'moderate' : 'low');
    }
}
