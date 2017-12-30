<?php

namespace SESP;

use DatabaseLogEntry;
use MWTimestamp;
use User;

class LogReader {

	/**
	 * Get the person who made the last for this page
	 *
	 * @return User
	 */
	public function getUser() {
		return User::newFromID( $this->getLog()->current()->user_id );
	}

	/**
	 * Get the date of the last entry in the log for this page
	 *
	 * @return Timestamp
	 */
	public function getDate() {
		return new MWTimestamp( $this->getLog()->current()->log_timestamp );
	}

	/**
	 * Get the status of the last entry in the log for this page
	 *
	 * @return Timestamp
	 */
	public function getStatus() {
		return $this->getLog()->current()->log_action;
	}

	/**
	 * Fetch the results using our conditions
	 *
	 * @return IResultWrapper
	 * @throws DBError
	 */
	protected function getLog() {
		if ( !$this->log ) {
			// @codingStandardsIgnoreLine DB_SLAVE needs to be kept for now
			$dbr = wfGetDB( DB_SLAVE );
			$this->log = $dbr->select(
				$query['tables'], $query['fields'], $query['conds'],
				__METHOD__, $query['options'], $query['join_conds']
			);
		}
		return $this->log;
	}

	/**
	 * Fetch the query for later calls
	 *
	 * @return array
	 */
	public function getQuery() {
		return $this->query;
	}

	// The parameters for our query.
	protected $query;

	// The current log
	protected $log;

	// Don't run multiple queries if we don't have to
	protected static $titleCache = [];

	/**
	 * Constructor for reading the log.
	 *
	 * @param Title $title page
	 * @param string $type of log (default: approval)
	 */
	public function __construct( Title $title, $type = 'approval' ) {
		if ( !isset( self::$titleCache[ $title->getDBKey() ] ) ) {
			$this->query = DatabaseLogEntry::getSelectQueryData();

			$this->query['conds'] = [
				'log_type' => $type,
				'log_title' => $title->getDBKey()
			];
			$this->query['options'] = [ 'ORDER BY' => 'log_timestamp desc' ];
		} else {
			$cache = self::$titleCache[ $title->getDBKey() ];
			$this->query = $cache->getQuery();
			$this->log = $cache->getLog();
		}
	}
}
