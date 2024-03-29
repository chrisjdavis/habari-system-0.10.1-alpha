<?php
/**
 * @package Filmio
 *
 */

namespace Filmio;

/**
 * Handler to run CronTab entries
 *
 */
class CronHandler extends ActionHandler
{
	/**
	 * Executes all cron jobs in the DB if there are any to run.
	 *
	 * @param boolean $async If true, allows execution to continue by making an asynchronous request to a cron URL
	 */
	static function run_cron( $async = false )
	{
		// check if it's time to run crons, and if crons are already running.
		$next_cron = DateTime::create( Options::get( 'next_cron' ) );
		$time = DateTime::create();
		if ( ( $next_cron->int > $time->int )
			|| ( Options::get( 'cron_running' ) && Options::get( 'cron_running' ) > microtime( true ) )
		) {
			return;
		}

		// cron_running will timeout in 10 minutes
		// round cron_running to 4 decimals
		$run_time = microtime( true ) + 600;
		$run_time = sprintf( "%.4f", $run_time );
		Options::set( 'cron_running', $run_time );

		if ( $async ) {
			// Timeout is really low so that it doesn't wait for the request to finish
			$cronurl = URL::get( 'cron',
				array(
					'time' => $run_time,
					'asyncronous' => Utils::crypt( Options::get( 'public-GUID' ) )
				)
			);
			$request = new RemoteRequest( $cronurl, 'GET', 1 );

			try {
				$request->execute();
			}
			catch ( RemoteRequest_Timeout $e ) {
				// the request timed out - we knew that would happen
			}
			catch ( \Exception $e ) {
				// some other error occurred. we still don't care
			}
		}
		else {
			// @todo why do we usleep() and why don't we just call act_poll_cron()?
			usleep( 5000 );
			if ( Options::get( 'cron_running' ) != $run_time ) {
				return;
			}

			$time = DateTime::create();
			$crons = DB::get_results(
				'SELECT * FROM {crontab} WHERE start_time <= ? AND next_run <= ? AND active != ?',
				array( $time->sql, $time->sql, 0 ),
				'CronJob'
			);
			if ( $crons ) {
				foreach ( $crons as $cron ) {
					$cron->execute();
				}
			}

			EventLog::log( _t( 'CronTab run completed.' ), 'debug', 'crontab', 'filmio', $crons );

			// set the next run time to the lowest next_run OR a max of one day.
			$next_cron = DB::get_value( 'SELECT next_run FROM {crontab} WHERE active != ? ORDER BY next_run ASC LIMIT 1', array( 0 ) );
			Options::set( 'next_cron', min( intval( $next_cron ), $time->modify( '+1 day' )->int ) );
			Options::set( 'cron_running', false );
		}
	}

	/**
	 * Handles asyncronous cron calls.
	 *
	 * @todo next_cron should be the actual next run time and update it when new
	 * crons are added instead of just maxing out at one day..
	 */
	function act_poll_cron()
	{
		Utils::check_request_method( array( 'GET', 'HEAD', 'POST' ) );

		$time = doubleval( $this->handler_vars['time'] );
		if ( $time != Options::get( 'cron_running' ) ) {
			return;
		}

		// allow script to run for 10 minutes. This only works on host with safe mode DISABLED
		if ( !ini_get( 'safe_mode' ) ) {
			set_time_limit( 600 );
		}
		$time = DateTime::create();
		$crons = DB::get_results(
			'SELECT * FROM {crontab} WHERE start_time <= ? AND next_run <= ? AND active != ?',
			array( $time->sql, $time->sql, 0 ),
			'CronJob'
		);

		if ( $crons ) {
			foreach ( $crons as $cron ) {
				$cron->execute();
			}
		}

		// set the next run time to the lowest next_run OR a max of one day.
		$next_cron = DB::get_value( 'SELECT next_run FROM {crontab} WHERE active != ? ORDER BY next_run ASC LIMIT 1', array( 0 ) );
		Options::set( 'next_cron', min( intval( $next_cron ), $time->modify( '+1 day' )->int ) );
		Options::set( 'cron_running', false );
	}

}

?>
