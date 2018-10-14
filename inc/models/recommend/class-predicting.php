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

	/**
	 * @throws \Phpml\Exception\InvalidArgumentException
	 */
	public function runPipeline() {

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
			false //probabilityEstimates
		);

//				$estimator = new NaiveBayes();
//				$estimator = new KNearestNeighbors();

		$training_pipeline = new Pipeline( $transformers, $estimator );
		$training_pipeline->train( $this->training_samples, $this->training_targets );

		/*
		|--------------------------------------------------------------------------
		| Predict
		|--------------------------------------------------------------------------
		|
		|
		|
		|
		*/

		$predicted = $training_pipeline->predict( $this->reporting_samples );

		/*
		|--------------------------------------------------------------------------
		| Report
		|--------------------------------------------------------------------------
		|
		|
		|
		|
		*/
		$report = new ClassificationReport( $this->reporting_targets, $predicted );

		$html = "<h2>Average</h2><table class='table table-responsive-lg'><tr><th>Subject</th><th>Average</th></tr>";
		foreach ( $report->getAverage() as $k => $v ) {
			$html .= sprintf( '<tr><td class="border">%s</td><td class="border">%s</td></tr>', $k, $v );
		};
		$html .= '</table>';

		$html .= "<h2>Precision</h2><table class='table table-responsive-lg'><tr><th>Subject</th><th>Precision</th></tr>";
		foreach ( $report->getPrecision() as $k => $v ) {
			$html .= sprintf( '<tr><td class="border">%1$s</td><td class="border">%2$s</td></tr>', $k, $v );
		}
		$html .= '</table>';

		$html .= "<h2>Recall</h2><table class='table table-responsive-lg'><tr><th>Subject</th><th>Recall</th></tr>";
		foreach ( $report->getRecall() as $k => $v ) {
			$html .= sprintf( '<tr><td class="border">%s</td><td class="border">%s</td></tr>', $k, $v );
		};
		$html .= '</table>';

		$html .= "<h2>F1 Score</h2><table class='table table-responsive-lg'><tr><th>Subject</th><th>F1 Score</th></tr>";
		foreach ( $report->getF1score() as $k => $v ) {
			$html .= sprintf( '<tr><td class="border">%s</td><td class="border">%s</td></tr>', $k, $v );
		};
		$html .= '</table>';

		$html .= "<h2>Support</h2><table class='table table-responsive-lg'><tr><th>Subject</th><th>Support</th></tr>";
		foreach ( $report->getSupport() as $k => $v ) {
			$html .= sprintf( '<tr><td class="border">%s</td><td class="border">%s</td></tr>', $k, $v );
		};
		$html .= '</table>';

		echo $html;

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
