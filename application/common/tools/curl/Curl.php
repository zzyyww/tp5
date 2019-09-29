<?php
/**
 * Created by PhpStorm.
 * User: zyw
 * Date: 2019-04-25
 */
namespace app\common\tools\curl;

use app\common\tools\curl\build\Base;


class Curl {
	protected $link;

	//获取实例
	protected function driver() {
		$this->link = new Base();
		return $this;
	}

	public function __call( $method, $params ) {
		if ( ! $this->link ) {
			$this->driver();
		}

		return call_user_func_array( [ $this->link, $method ], $params );
	}

	public static function single() {
		static $link = null;
		if ( is_null( $link ) ) {
			$link = new static();
		}

		return $link;
	}

	public static function __callStatic( $name, $arguments ) {
		return call_user_func_array( [ static::single(), $name ], $arguments );
	}
}