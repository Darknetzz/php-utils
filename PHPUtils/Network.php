<?php

declare(strict_types=1);

namespace PHPUtils;

class Network extends Base {
  
  /**
   * Convert CIDR notation to IP range.
   *
   * @param  string $cidr The CIDR notation.
   * @return array The IP range as an array with two elements: lower IP and upper IP.
   */
  public function cidrToRange(string $cidr): array {
    $range = array();
    $cidr = explode('/', $cidr);
    $range[0] = long2ip((ip2long($cidr[0])) & ((-1 << (32 - (int)$cidr[1]))));
    $range[1] = long2ip((ip2long($range[0])) + pow(2, (32 - (int)$cidr[1])) - 1);
    return $range;
  }

  /**
   * Check if an IP is within a given range.
   *
   * @param  string $ip The IP address to check.
   * @param  string $lowerip The lower IP address of the range.
   * @param  string $upperip The upper IP address of the range.
   * @return bool|null True if the IP is within the range, false otherwise, null if invalid IPs.
   */
  public function ipInRange(string $ip, string $lowerip, string $upperip): ?bool {
    $ip         = ip2long($ip);
    $lower_long = ip2long($lowerip);
    $upper_long = ip2long($upperip);

    if ($ip === false || $lower_long === false || $upper_long === false) {
      return null;
    }

    if ($lower_long <= $ip && $ip <= $upper_long) {
      return true;
    }
    return false;
  }

  /**
   * Get the user's IP address.
   *
   * @param  string|null $reverse_proxy The IP address of the reverse proxy server.
   * @param  bool $return_array Whether to return the result as an array or not.
   * @param  bool $die_if_empty Whether to die if the user's IP cannot be determined.
   * @return string|array|null The user's IP address or an array with 'type' and 'userip' keys if $return_array is true. Returns null if $die_if_empty is false and the user's IP cannot be determined.
   */
  public function getUserIP(
    ?string $reverse_proxy = null,
    bool $return_array = false,
    bool $die_if_empty = false
  ): string|array|null {
    $ra     = (!empty($_SERVER['REMOTE_ADDR'])          ? $_SERVER['REMOTE_ADDR']          : null);
    $ff     = (!empty($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : null);
    $type   = (!empty($ff)                              ? "proxy"                          : "direct");
    $userip = (!empty($ff)                              ? $ff                              : $ra);

    if (empty($userip) && $die_if_empty !== false) {
      throw new \RuntimeException("getUserIP: Unable to get IP from user.");
    }

    if (!empty($reverse_proxy) && $ra == $reverse_proxy) {
      $type   = "proxy";
    }

    if ($return_array !== false) {
      return ["type" => $type, "userip" => $userip];
    }
    return $userip;
  }

  /**
   * Get the server's IP address.
   *
   * @param  bool $return_array Whether to return the result as an array or not.
   * @param  bool $die_if_empty Whether to die if the server's IP cannot be determined.
   * @return string|array|null The server's IP address or an array with 'type' and 'serverip' keys if $return_array is true. Returns null if $die_if_empty is false and the server's IP cannot be determined.
   */
  public function getServerIP(
    bool $return_array = false,
    bool $die_if_empty = false
  ): string|array|null {
    $serverip = (!empty($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : null);

    if (empty($serverip) && $die_if_empty !== false) {
      throw new \RuntimeException("getServerIP: Unable to get IP from server.");
    }

    if (empty($serverip) && $die_if_empty === false) {
      return null;
    }

    if ($return_array !== false) {
      return ["type" => "server", "serverip" => $serverip];
    }
    return $serverip;
  }

  /**
   * Check if the user is using a reverse proxy.
   *
   * @param  string|null $proxy The IP address of the reverse proxy server.
   * @return bool True if the user is using a reverse proxy, false otherwise.
   */
  public function usesReverseProxy(?string $proxy = null): bool {
    $ra     = (!empty($_SERVER['REMOTE_ADDR'])          ? $_SERVER['REMOTE_ADDR']          : null);
    $ff     = (!empty($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : null);

    if (!empty($ff)) {
      return true;
    }
    if ($ra == $proxy) {
      return true;
    }
    return false;
  }

}