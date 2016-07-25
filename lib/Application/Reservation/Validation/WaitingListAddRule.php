<?php
/**
Copyright 2013-2015 Nick Korbel

This file is part of Booked Scheduler is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Booked Scheduler.  If not, see <http://www.gnu.org/licenses/>.
*/

require_once(ROOT_DIR . 'Domain/ReservationWaitingListEntry.php');

class WaitingListAddRule implements IReservationValidationRule
{
	/**
	 * @param ReservationSeries $reservationSeries
	 * @return ReservationRuleResult
	 */
	public function Validate($reservationSeries)
	{
		if ($reservationSeries->StatusId() == ReservationStatus::Pending)
		{
			$reservationSeries->SetAddedToWaitingList(new ReservationWaitingListEntry($reservationSeries->UserId(), $reservationSeries->Title(), $reservationSeries->Description()));
			$reservationSeries->SetUserId(2); /// ToDo variable userId for scheduler
			$reservationSeries->SetTitle('');
			$reservationSeries->SetDescription('');
		}

		return new ReservationRuleResult();
	}
}

?>