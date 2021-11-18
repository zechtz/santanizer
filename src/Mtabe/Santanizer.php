<?php

namespace Mtabe;

class Santanizer
{
  const MESSAGE_ERROR_DESTRUCTIVE_WORDS = "SQL QUERY CONTAINS THE FOLLOWING DESCTRUCTIVE WORDS";
  /**
   *
   * return sql query without destructive words
   * e.g given the following sql $query = 'insert into users
   * sanitize($query, ['name' => 'John Doe', 'age' => 20, 'code' => 322])
   * should return "SQL QUERY CONTAINS THE FOLLOWING DESCTRUCTIVE WORDS", "insert"
   * @param string $query
   * @return $query
   *
   */
  public static function sanitize($query, $params = [])
  {
    // select * from table where code = #P{value}
    // $query = select * from table where code = #P{value}
    // $query = preg_replace("/(\$\w+\{\w+\})/", #value, $query);
    // Matches any value inside {} and saves in $matches array
    // preg_match returns 1 if there's a match or 0 if no match
    // then saves the match in the $matches array
    // remove new line characters and tabs and all
    // two or more spaces with single space
    // check if query contains either drop, update, delete,
    // truncate and return error message if it does

    if (empty($params)) {
      $result = self::queryHasDestructiveWords($query) ? self::queryHasDestructiveWords($query) : $query;
      return $result;
    } else {
      $result = preg_replace_callback(
        '/\#P\{(.*?)\}/',
        function ($el) use ($params) {
          return isset($params[$el[1]]) ? $params[$el[1]] : $el[0];
        },
        $query
      );
      $result = self::allParamsHaveNotBeenReplaced($result) ? self::allParamsHaveNotBeenReplaced($result) : $result;
      return $result;
    }
  }

  private static function queryHasDestructiveWords($query)
  {
    if (preg_match_all('/(insert|update\b|drop|delete\b|truncate|add|create|replace|insert|commit|grant\b|constraint|set)/i', $query, $matches)) {
      $badWords = array_pop($matches);
      throw new \Exception(MESSAGE_ERROR_DESTRUCTIVE_WORDS . " \n" . join("\r\n", $badWords));
    } else {
      return $query;
    }
  }

  private  static function allParamsHaveNotBeenReplaced($query)
  {
    if (preg_match_all('/(\#P\{.*?\})/', $query, $matches)) {
      $unReplacedPlaceholders = array_pop($matches);
      throw new \Exception(
        MESSAGE_ERROR_UNREPLACED_PARAMS . " \n" . join(
          "\r\n",
          $unReplacedPlaceholders
        )
      );
    } else {
      return $query;
    }
  }
}
