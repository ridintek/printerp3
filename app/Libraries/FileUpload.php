<?php

declare(strict_types=1);

namespace App\Libraries;

use App\Models\Attachment;

/**
 * File Upload class.
 */
class FileUpload
{
  protected $file = null;
  /**
   * @var array
   */
  protected $files = [];
  /**
   * @var bool
   */
  protected $isMoved;

  public function __construct()
  {
    if (is_cli()) die("FileUpload() class cannot be run in CLI mode.");

    $this->files = $_FILES;
  }

  public function files()
  {
    return $this->files;
  }

  /**
   * Check if file has been uploaded and has size more than zero.
   * @param string $filename Filename.
   */
  public function has($filename)
  {
    $this->isMoved = false;

    if (isset($this->files[$filename]) && $this->files[$filename]['size'] > 0) {
      $this->file = $this->files[$filename];
      return true;
    }

    return false;
  }

  public function getExtension()
  {
    if ($this->file) {
      if (strpos($this->getName(), '.') !== false) {
        $s = explode('.', $this->getName());
        $len = count($s);

        return '.' . $s[$len - 1];
      }
    }

    return null;
  }

  public function getRandomName()
  {
    if ($this->file) {
      return bin2hex(random_bytes(16)) . $this->getExtension();
    }

    return null;
  }

  public function getName()
  {
    if ($this->file) {
      return $this->file['name'];
    }

    return null;
  }

  /**
   * Get file size.
   * @param string unit Unit to check. byte, kb, mb, gb
   */
  public function getSize($unit = 'byte')
  {
    if ($this->file) {
      switch ($unit) {
        case 'kb':
          $acc = 1024;
          break;
        case 'mb':
          $acc = (1024 * 1024);
          break;
        case 'gb':
          $acc = (1024 * 1024 * 1024);
          break;
        case 'byte':
        default:
          $acc = 1;
      }

      return ceil($this->file['size'] / $acc);
    }

    return null;
  }

  public function getTempName()
  {
    if ($this->file) {
      return $this->file['tmp_name'];
    }

    return null;
  }

  public function getType()
  {
    if ($this->file) {
      return $this->file['type'];
    }

    return null;
  }

  /**
   * Check if file has been moved or not.
   * @return bool
   */
  public function isMoved()
  {
    return $this->isMoved;
  }

  public function move($path, $newName = null)
  {
    if ($this->file) {
      $path = rtrim($path, '/') . '/';
      checkPath($path);
      $newName = ($newName ?? $this->getName());

      if (move_uploaded_file($this->getTempName(), $path . $newName)) {
        $this->isMoved = true;
        return true;
      }
    }

    return false;
  }

  /**
   * Store file to attachment table as BLOB.
   * @param string $filename Filename to store. Use default filename if omitted.
   * @param string $hashname Update record if present. Use random hashname if omitted.
   * @return string|null Return stored hashname.
   */
  public function store($filename = null, $hashname = null)
  {
    if ($hashname) {
      $attachment = Attachment::getRow(['hashname' => $hashname]);

      if ($attachment) {
        $res = Attachment::update((int)$attachment->id, [
          'filename'  => ($filename ?? $this->getName()),
          'hashname'  => $attachment->hashname,
          'mime'      => $this->getType(),
          'data'      => file_get_contents($this->getTempName()),
          'size'      => $this->getSize()
        ]);

        if (!$res) {
          return null;
        }

        return $attachment->hashname;
      }
    }

    $data = [
      'filename'  => ($filename ?? $this->getName()),
      'hashname'  => ($hashname ?? _uid()),
      'mime'      => $this->getType(),
      'data'      => file_get_contents($this->getTempName()),
      'size'      => $this->getSize()
    ];

    if (!Attachment::add($data)) {
      return null;
    }

    return $data['hashname'];
  }

  /**
   * Store file with random name to attachment table as BLOB.
   * @return string Return stored hashname.
   */
  public function storeRandom()
  {
    return $this->store($this->getRandomName());
  }
}
