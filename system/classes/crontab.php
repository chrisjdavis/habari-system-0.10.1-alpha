<?php
/**
 * @package Filmio
 *
 */

	namespace Filmio;

	/**
	 * Static class to build and read cron entries
	 */
	class CronTab {

		/**
		 * Get a Cron Job by name or id from the Database.
		 *
		 * @param mixed $name The name or id of the cron job to retreive.
		 * @return \Filmio\CronJob The cron job retreived from the DB
		 */
		static function get_cronjob( $name )
		{
			if ( is_int( $name ) ) {
				$cron = DB::get_row( 'SELECT * FROM {crontab} WHERE cron_id = ?', array( $name ), '\Filmio\CronJob' );
			}
			else {
				$cron = DB::get_row( 'SELECT * FROM {crontab} WHERE name = ?', array( $name ), '\Filmio\CronJob' );
			}
			return $cron;
		}

		/**
		 * Delete a Cron Job by name or id from the Database.
		 *
		 * @param mixed $name The name or id of the cron job to delete.
		 * @return bool Wheather or not the delete was successfull
		 */
		static function delete_cronjob( $name )
		{
			$cron = self::get_cronjob( $name );
			if ( $cron ) {
				return $cron->delete();
			}
			return false;
		}

		/**
		 * Add a new cron job to the DB.
		 *
		 * @see CronJob
		 * @param array $paramarray A paramarray of cron job feilds.
		 * @return \Filmio\CronJob
		 */
		static function add_cron( $paramarray )
		{
			// Delete any existing job with this same name
			if($job = CronTab::get_cronjob($paramarray['name'])) {
				$job->delete();
			}

			$cron = new CronJob( $paramarray );
			$result = $cron->insert();

			//If the new cron should run earlier than the others, rest next_cron to its strat time.
			$next_cron = DB::get_value( 'SELECT next_run FROM {crontab} ORDER BY next_run ASC LIMIT 1', array() );
			if ( intval( Options::get( 'next_cron' ) ) > intval( $next_cron ) ) {
				Options::set( 'next_cron', $next_cron );
			}
			return $result ? $cron : false;
		}

		/**
		 * Add a new cron job to the DB, that runs only once.
		 *
		 * @param string $name The name of the cron job.
		 * @param mixed $callback The callback function or plugin action for the cron job to execute.
		 * @param DateTime $run_time The time to execute the cron.
		 * @param string $description The description of the cron job.
		 * @return \Filmio\CronJob
		 */
		static function add_single_cron( $name, $callback, $run_time, $description = '' )
		{
			$paramarray = array(
				'name' => $name,
				'callback' => $callback,
				'start_time' => $run_time,
				'end_time' => $run_time, // only run once
				'description' => $description
			);
			return self::add_cron( $paramarray );
		}

		/**
		 * Add a new cron job to the DB, that runs hourly.
		 *
		 * @param string $name The name of the cron job.
		 * @param mixed $callback The callback function or plugin action for the cron job to execute.
		 * @param string $description The description of the cron job.
		 * @return \Filmio\CronJob
		 */
		static function add_hourly_cron( $name, $callback, $description = '' )
		{
			$paramarray = array(
				'name' => $name,
				'callback' => $callback,
				'increment' => 3600, // one hour
				'description' => $description
			);
			return self::add_cron( $paramarray );
		}

		/**
		 * Add a new cron job to the DB, that runs daily.
		 *
		 * @param string $name The name of the cron job.
		 * @param mixed $callback The callback function or plugin action for the cron job to execute.
		 * @param string $description The description of the cron job.
		 * @return \Filmio\CronJob
		 */
		static function add_daily_cron( $name, $callback, $description = '' )
		{
			$paramarray = array(
				'name' => $name,
				'callback' => $callback,
				'increment' => 86400, // one day
				'description' => $description
			);
			return self::add_cron( $paramarray );
		}

		/**
		 * Add a new cron job to the DB, that runs weekly.
		 *
		 * @param string $name The name of the cron job.
		 * @param mixed $callback The callback function or plugin action for the cron job to execute.
		 * @param string $description The description of the cron job.
		 * @return \Filmio\CronJob
		 */
		static function add_weekly_cron( $name, $callback, $description = '' )
		{
			$paramarray = array(
				'name' => $name,
				'callback' => $callback,
				'increment' => 604800, // one week (7 days)
				'description' => $description
			);
			return self::add_cron( $paramarray );
		}

		/**
		 * Add a new cron job to the DB, that runs monthly.
		 *
		 * @param string $name The name of the cron job.
		 * @param mixed $callback The callback function or plugin action for the cron job to execute.
		 * @param string $description The description of the cron job.
		 * @return \Filmio\CronJob
		 */
		static function add_monthly_cron( $name, $callback, $description = '' )
		{
			$paramarray = array(
				'name' => $name,
				'callback' => $callback,
				'increment' => 2592000, // one month (30 days)
				'description' => $description
			);
			return self::add_cron( $paramarray );
		}

	}

?>
