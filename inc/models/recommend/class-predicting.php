<?php
/**
 * Project: opentextbooks
 * Project Sponsor: BCcampus <https://bccampus.ca>
 * Copyright 2012-2018 Brad Payne <https://bradpayne.ca>
 * Date: 2018-10-08
 * Licensed under GPLv3, or any later version
 *
 * @author Brad Payne
 * @package OPENTEXTBOOKS
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright (c) 2012-2018, Brad Payne
 */

namespace BCcampus\Opentextbooks\Models\Recommend;

use Phpml\Classification\SVC;
use Phpml\Classification\NaiveBayes;
use Phpml\Classification\KNearestNeighbors;
use Phpml\Exception\InvalidArgumentException;
use Phpml\FeatureExtraction\TfIdfTransformer;
use Phpml\Metric\ClassificationReport;
use Phpml\Pipeline;
use Phpml\FeatureExtraction\TokenCountVectorizer;
use Phpml\Tokenization\WordTokenizer;
use Phpml\FeatureExtraction\StopWords\English;
use Phpml\SupportVectorMachine\Kernel;


class Predicting {

	private $training_samples;
	private $training_targets;
	private $reporting_samples;
	private $reporting_targets;

	public function __construct( $samples, $targets, $reporting_samples, $reporting_targets ) {
		$this->training_samples  = $samples;
		$this->training_targets  = $targets;
		$this->reporting_targets = $reporting_targets;
		$this->reporting_samples = $reporting_samples;
	}

	public function trainTheClassifier( $probability = false ) {
		$transformers = [
			new TokenCountVectorizer( new WordTokenizer(), new English() ),
			new TfIdfTransformer(),
		];

		$estimator = new SVC(
			Kernel::LINEAR,
			1.0, //cost
			3, //degree
			NULL, //gamma
			0.0, //coef0
			0.001, //tolerance
			100, //cacheSize
			true, //shrinking
			$probability //probabilityEstimates
		);

		//				$estimator = new NaiveBayes();
		//				$estimator = new KNearestNeighbors();

		$training_pipeline = new Pipeline( $transformers, $estimator );
		$training_pipeline->train( $this->training_samples, $this->training_targets );

		return $training_pipeline;
	}

	/**
	 * @param $reporting_targets
	 * @param $predicted
	 *
	 * @return \Phpml\Metric\ClassificationReport|string
	 */
	public function runReport( $reporting_targets, $predicted ) {
		$report = '';
		try {
			$report = new ClassificationReport( $reporting_targets, $predicted );
		} catch ( InvalidArgumentException $e ) {
			error_log( $e->getMessage() );
		}

		return $report;

	}

	/**
	 * @throws \Phpml\Exception\LibsvmCommandException
	 */
	public function runProbability() {
		$transformers = [
			new TokenCountVectorizer( new WordTokenizer(), new English() ),
		];

		$estimator = new SVC(
			Kernel::LINEAR,
			1.0, //cost
			3, //degree
			NULL, //gamma
			0.0, //coef0
			0.001, //tolerance
			100, //cacheSize
			true, //shrinking
			true //probabilityEstimates
		);

		/*
		|--------------------------------------------------------------------------
		| No Pipeline
		|--------------------------------------------------------------------------
		|
		| manually recreating pipeline process
		|
		|
		*/
		// Transform training data
		foreach ( $transformers as $transformer ) {
			$transformer->fit( $this->training_samples, $this->training_targets );
			$transformer->transform( $this->training_samples );
		}

		$estimator->train( $this->training_samples, $this->training_targets );

		// Transform the samples
		foreach ( $transformers as $transformer ) {
			$transformer->transform( $this->reporting_samples );
		}

		$yay = $estimator->predictProbability( $this->reporting_samples );

		echo "<pre>";
		print_r( $yay );
		echo "</pre>";

	}
}
