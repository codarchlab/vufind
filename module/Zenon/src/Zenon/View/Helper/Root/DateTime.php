<?php

namespace Zenon\View\Helper\Root;

class DateTime extends \VuFind\View\Helper\Root\DateTime
{
    /**
     * correctly calculates end of months when we shift to a shorter or longer month
     * workaround for http://php.net/manual/en/datetime.add.php#example-2489
     *
     * Makes the assumption that shifting from the 28th Feb +1 month is 31st March
     * Makes the assumption that shifting from the 28th Feb -1 month is 31st Jan
     * Makes the assumption that shifting from the 29,30,31 Jan +1 month is 28th (or 29th) Feb
     *
     *
     * @param DateTime $aDate
     * @param int $months positive or negative
     *
     * @return DateTime new instance - original parameter is unchanged
     */

    public function monthShifter (\DateTime $aDate,$months) {
        $dateA = clone($aDate);
        $dateB = clone($aDate);
        $plusMonths = clone($dateA->modify($months . ' Month'));

        //check whether reversing the month addition gives us the original day back
        if ($dateB != $dateA->modify($months*-1 . ' Month')) {
            $result = $plusMonths->modify('last day of last month');

        } elseif ($aDate == $dateB->modify('last day of this month')) {
            $result =  $plusMonths->modify('last day of this month');

        } else {
            $result = $plusMonths;
        }

        return $result;
    }

}
