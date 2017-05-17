<?php

namespace Acquia\Blt\Robo\Datastore;

/**
 * Interface DataStoreInterface.
 */
interface DataStoreInterface {

  /**
   * Reads retrieves data from the store.
   *
   * @param string $key
   *   A key.
   *
   * @return mixed The value fpr the given key or null.
   */
  public function get($key);

  /**
   * Saves a value with the given key.
   *
   * @param string $key
   *   A key.
   * @param mixed $data
   *   Data to save to the store.
   */
  public function set($key, $data);

  /**
   * Checks if a key is in the store.
   *
   * @param string $key
   *   A key.
   *
   * @return bool Whether a value exists with the given key
   */
  public function has($key);

  /**
   * Remove value from the store.
   *
   * @param string $key
   *   A key.
   */
  public function remove($key);

  /**
   * Return a list of all keys in the store.
   *
   * @return array A list of keys
   */
  public function keys();

}
