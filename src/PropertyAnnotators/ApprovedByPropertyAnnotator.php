<?php

namespace SESP\PropertyAnnotators;

use ApprovedRevs;
use SESP\AppFactory;
use SESP\PropertyAnnotator;
use SESP\LogReader;
use SMW\DIWikiPage;
use SMWDataItem as DataItem;
use SMW\DIProperty;
use SMW\SemanticData;
use User;

/**
 * @private
 * @ingroup SESP
 *
 * @license GNU GPL v2+
 */
class ApprovedByPropertyAnnotator implements PropertyAnnotator {

	/**
	 * Predefined property ID
	 */
	const PROP_ID = '___APPROVEDBY';

	/**
	 * @var AppFactory
	 */
	private $appFactory;

	/**
	 * @var Integer|null
	 */
	private $approvedBy;

	/**
	 * @param AppFactory $appFactory
	 */
	public function __construct( AppFactory $appFactory ) {
		$this->appFactory = $appFactory;
	}

	/**
	 * @since 2.0
	 *
	 * @param User $approvedBy
	 */
	public function setApprovedRev( User $approvedBy ) {
		$this->approvedBy = $approvedBy;
	}

	/**
	 * {@inheritDoc}
	 */
	public function isAnnotatorFor( DIProperty $property ) {
		return $property->getKey() === self::PROP_ID;
	}

	/**
	 * {@inheritDoc}
	 */
	public function addAnnotation(
		DIProperty $property, SemanticData $semanticData
	) {
		if ( $this->approvedBy === null && class_exists( 'ApprovedRevs' ) ) {
			$logReader = new LogReader(
				$semanticData->getSubject()->getTitle(), 'approval'
			);
			$this->approvedBy = $logReader->getUser();
		}

		if ( $this->approvedBy ) {
			$userPage = $this->approvedBy->getUserPage();
			if ( $userPage instanceof Title ) {
				$dataItem = DIWikiPage::newFromTitle( $userPage );
			}
		}

		if ( $dataItem instanceof DataItem ) {
			$semanticData->addPropertyObjectValue( $property, $dataItem );
		} else {
			$semanticData->removeProperty( $property );
		}
	}
}
