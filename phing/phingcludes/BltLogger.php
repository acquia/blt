<?php

require_once 'phing/listener/AnsiColorLogger.php';

class BltLogger extends AnsiColorLogger {
  /**
   *  Sets the start-time when the build started. Used for calculating
   *  the build-time.
   *
   * @param BuildEvent $event
   * @internal param The $object BuildEvent
   */
  public function buildStarted(BuildEvent $event)
  {
    $this->startTime = Phing::currentTimeMillis();
  }

  /**
   * Get the message to return when a build succeeded.
   * @return string The classic "BUILD FINISHED"
   */
  protected function getBuildSuccessfulMessage()
  {
    return "BUILD FINISHED";
  }

  /**
   *  Prints whether the build succeeded or failed, and any errors that
   *  occurred during the build. Also outputs the total build-time.
   *
   * @param BuildEvent $event
   * @internal param The $object BuildEvent
   * @see    BuildEvent::getException()
   */
  public function buildFinished(BuildEvent $event)
  {
    $error = $event->getException();
    if ($error === null) {
      $msg = PHP_EOL . $this->getBuildSuccessfulMessage();
    } else {
      $msg = PHP_EOL . $this->getBuildFailedMessage();
      self::throwableMessage($msg, $error, Project::MSG_VERBOSE <= $this->msgOutputLevel);
    }

    $total_time = self::formatTime(Phing::currentTimeMillis() - $this->startTime);
    $msg .= "; $total_time";

    if ($error === null) {
      $this->printMessage($msg, $this->out, Project::MSG_VERBOSE);
    } else {
      $this->printMessage($msg, $this->err, Project::MSG_ERR);
    }
  }

  /**
   *  Prints the current target name
   *
   * @param BuildEvent $event
   * @internal param The $object BuildEvent
   * @see    BuildEvent::getTarget()
   */
  public function targetStarted(BuildEvent $event)
  {
    if (Project::MSG_INFO <= $this->msgOutputLevel
      && $event->getTarget()->getName() != ''
    ) {
      $showLongTargets = $event->getProject()->getProperty("phing.showlongtargets");

      $msg = $event->getProject()->getName() . ' > ' . $event->getTarget()->getName(
        ) . ($showLongTargets ? ' [' . $event->getTarget()->getDescription() . ']' : '') . ':';
      $this->printMessage($msg, $this->out, $event->getPriority());
    }
  }
}
