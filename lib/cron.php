<?php
	class SlackCron {

		private $minute;
		private $hour;

		public function SlackCron() {
			$this->setTime(time());
		}

		/**
		 * Checks a minute/hour expression to determine if it matches the current time.
		 *
		 * @param {Array|Int|Null|String} $minute An expression to match the current minute.
		 * @param {Array|Int|Null|String} $hour An expression to match the current hour.
		 * @return {Boolean} Whether all conditions are satisfied.
		 */
		public function check($minute = NULL, $hour = NULL) {
			// If an argument is not specified, its check returns true.
			$satisfiesMinute = ( isset($minute) ) ? $this->checkMinute($minute) : TRUE;
			$satisfiesHour = ( isset($hour) ) ? $this->checkHour($hour) : TRUE;
			return ( $satisfiesMinute && $satisfiesHour );
		}

		private function checkMinute($minute) {
			return $this->checkInterval($minute, $this->minute);
		}

		private function checkHour($hour) {
			return $this->checkInterval($hour, $this->hour);
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
		private function setTime($time) {
			if (is_int($time)) {
				$this->minute = (int) date('i', $time);
				$this->hour = (int) date('G', $time);
			}
		}
	}