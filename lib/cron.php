<?php
	class SlackCron {

		private $minute;
		private $hour;
		private $day;

		private $now;

		/**
		 * Instantiates a new SlackCron object
		 *
		 * @param {Array|Int|Null|String} $minute An expression to match the current minute.
		 * @param {Array|Int|Null|String} $hour An expression to match the current hour.
		 * @param {Array|Int|Null|String} $day An expression to match the current day.
		 */
		public function SlackCron($minute = NULL, $hour = NULL, $day = NULL) {
			
			$this->minute = $minute;
			$this->hour = $hour;
			$this->day = $day;

			$this->now = new stdClass();
			$this->setTime();
		}

		/**
		 * Checks a minute/hour expression to determine if it matches the current time.
		 *
		 * @return {Boolean} Whether all conditions are satisfied.
		 */
		public function isDue() {
			// If an argument is not specified, its check returns true.
			$satisfiesMinute = ( isset($this->minute) ) ? $this->checkMinute($this->minute) : TRUE;
			$satisfiesHour = ( isset($this->hour) ) ? $this->checkHour($this->hour) : TRUE;
			$satisfiesDay = ( isset($this->day) ) ? $this->checkDay($this->day) : TRUE;

			return ( $satisfiesMinute && $satisfiesHour && $satisfiesDay);
		}

		private function checkMinute($minute) {
			return $this->checkInterval($minute, $this->now->minute);
		}

		private function checkHour($hour) {
			return $this->checkInterval($hour, $this->now->hour);
		}
		private function checkDay($day) {
			return $this->checkInterval($day, $this->now->day);
		}

		/**
		 * Checks a cron-like value against a control value.
		 *
		 * @param {Array|Int|String} An array, integer, or string representing a
		 *     range, single, or modulo cron expression
		 * @param {Int} $control Value against which to compare $interval
		 * @return {Boolean} Whether the interval matches the control
		 */
		private function checkInterval($interval, $control) {
			if( is_numeric($interval) ) {
				// Treat as a single number
				return $interval == $control;
			}
			elseif( is_array($interval) ) {
				// Treat as a set of numbers
				return in_array($control, $interval);
			}
			elseif( $interval == '*') {
				// Treat as a cron wildcard, always true
				return TRUE;
			}
			elseif( preg_match('/[0-9]{1,2}\,[0-9]{1,2}/', $interval) ) {
				// Treat as a cron set
				$set = explode(',', $interval);
				return in_array($control, $set);
			}
			elseif( preg_match('/^([0-9]{1,2})-([0-9]{1,2})$/', $interval) ) {
				// Treat as a cron range
				preg_match('/^([0-9]{1,2})-([0-9]{1,2})$/', $interval, $matches);
				$range = range($matches[1],$matches[2]);

				return in_array($control, $range);
			}
			elseif( preg_match('/^\*\/([0-9]{1,2})$/', $interval) ) {
				// Treat as a cron interval (like */5 for every 5 minutes)
				preg_match('/^\*\/([0-9]{1,2})$/', $interval, $matches);
				$interval = $matches[1];

				return ($control % $interval == 0);
			}
			else {
				return FALSE;
			}
		}

		/**
		 * Sets the current minute and hour
		 *
		 * @param {Int} $time A UNIX timestamp
		 *
		 */
		private function setTime() {
			$time = time();

			$this->now->minute = (int) date('i', $time);
			$this->now->hour = (int) date('G', $time);
			$this->now->day = (int) date('N', $time);

		}
	}