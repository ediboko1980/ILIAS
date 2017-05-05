<?php
/* Copyright (c) 2017 Stefan Hecken <stefan.hecken@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Validation\Constraints;
use ILIAS\Validation\Constraint;
use ILIAS\Data;
use ILIAS\Data\Result;

class GreaterThan extends Custom implements Constraint {
	const ERROR_MESSAGE = "The checked value is not greater.";

	/**
	 * @var int
	 */
	protected $min;

	public function __construct($min, Data\Factory $data_factory) {
		assert('is_int($min)');
		$this->min = $min;
		$this->data_factory = $data_factory;
	}

	/**
	 * @inheritdoc
	 */
	public function accepts($value) {
		return $value > $this->min;
	}

	/**
	 * Get the problem message
	 *
	 * @return string
	 */
	public function getErrorMessage() {
		if($this->error !== null) {
			return call_user_func($this->error);
		}

		return self::ERROR_MESSAGE;
	}
}