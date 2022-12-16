<?php

namespace Fagforbundet\AntiVirusApiClientBundle\Entity\Response;

enum FileScanResult : string {
  case VIRUS = 'VIRUS';
  case CLEAN = 'CLEAN';
}
