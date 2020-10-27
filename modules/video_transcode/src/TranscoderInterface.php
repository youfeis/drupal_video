<?php

/**
 * @file
 * Provides Drupal\video_transcode\TranscoderInterface
 */

namespace Drupal\video_transcode;

use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Defines an interface for transcoder plugins.
 */
interface TranscoderInterface extends PluginInspectionInterface {

  /**
   * Get the transcoded files.
   *
   * @return array
   *   Array of transcoded files.
   */
  public function getOutputFiles();

  /**
   * Get the video thumbnails.
   *
   * @return array
   *   Array of video thumbnails.
   */
  public function getVideoThumbnails();

}
