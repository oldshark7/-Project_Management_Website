<?php

namespace App\Services;

class HrSummaryService
{
    public function calculate($memberWorkloads)
    {
        $avgWorkload = 0;
        $overloadCount = 0;
        $optimalCount = 0;
        $underloadCount = 0;

        if ($memberWorkloads->isNotEmpty()) {

            foreach ($memberWorkloads as $member) {
                $wl = $member['total_workload'];
                if ($wl > 85) {$overloadCount++;} 
                elseif ($wl >= 60) {$optimalCount++;} 
                else {$underloadCount++;}
            }

            $avgWorkload = round($memberWorkloads->avg('total_workload'));
        }

        return compact(
            'avgWorkload',
            'overloadCount',
            'optimalCount',
            'underloadCount'
        );
    }
}
