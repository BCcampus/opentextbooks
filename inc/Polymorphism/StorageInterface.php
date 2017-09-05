<?php

namespace BCcampus\OpenTextBooks\Polymorphism;

interface StorageInterface {

	function save( $data );
	function load();
	function remove();
}
