<?php
/**
Copyright 2011-2015 Nick Korbel
Copyright 2016-2017 Stefan Braun

This file is part of Booked Scheduler.

Booked Scheduler is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Booked Scheduler is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Booked Scheduler.  If not, see <http://www.gnu.org/licenses/>.
*/

require_once(ROOT_DIR . 'Controls/Dashboard/UpcomingReservations.php');

class UpcomingReservationsPresenter
{
	/**
	 * @var IUpcomingReservationsControl
	 */
	private $control;

	/**
	 * @var IReservationViewRepository
	 */
	private $repository;

	/**
	 * @var int
	 */
	private $searchUserId = ReservationViewRepository::ALL_USERS;

	/**
	 * @var int
	 */
	private $searchUserLevel = ReservationUserLevel::ALL;

	public function __construct(IUpcomingReservationsControl $control, IReservationViewRepository $repository)
	{
		$this->control = $control;
		$this->repository = $repository;
	}

	public function SetSearchCriteria($userId, $userLevel)
	{
		$this->searchUserId = $userId;
		$this->searchUserLevel = $userLevel;
	}

	public function PageLoad()
	{
		$user = ServiceLocator::GetServer()->GetUserSession();
		$timezone = $user->Timezone;

		$now = Date::Now();
		$today = $now->ToTimezone($timezone)->GetDate();
		$dayOfWeek = $today->Weekday();

		$lastDate = $now->AddDays(13-$dayOfWeek-1);
		$reservations = $this->repository->GetReservationList($now, $lastDate, $this->searchUserId, $this->searchUserLevel);
		$tomorrow = $today->AddDays(1);

		$startOfNextWeek = $today->AddDays(7-$dayOfWeek);

		$labelFactory = new SlotLabelFactory($user);
		$todays = array();
		$tomorrows = array();
		$thisWeeks = array();
		$nextWeeks = array();

		/* @var $reservation ReservationItemView */
		foreach ($reservations as $reservation)
		{
			$start = $reservation->StartDate->ToTimezone($timezone);

			if ($reservation->IsWaitingListActive())
			{
				$reservation->FirstName = $labelFactory->Format($reservation, '{waitinglist}');
				$reservation->LastName = '';

				$entry = $reservation->GetWaitingListEntry($user->UserId);
				if (isset($entry))
				{
					$reservation->Title = $entry->Title();
					$reservation->Description = $entry->Description();
					$reservation->UserId = $entry->UserId();
				}
			}

			if ($start->DateEquals($today))
			{
				$todays[] = $reservation;
			}
			else if ($start->DateEquals($tomorrow))
			{
				$tomorrows[] = $reservation;
			}
			else if ($start->LessThan($startOfNextWeek))
			{
				$thisWeeks[] = $reservation;
			}
			else
			{
				$nextWeeks[] = $reservation;
			}
		}

		$this->control->SetTotal(count($reservations));
		$this->control->SetTimezone($timezone);
		$this->control->SetUserId($user->UserId);

		$this->control->BindToday($todays);
		$this->control->BindTomorrow($tomorrows);
		$this->control->BindThisWeek($thisWeeks);
		$this->control->BindNextWeek($nextWeeks);
	}
}