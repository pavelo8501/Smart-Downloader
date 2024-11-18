<?php

namespace SmartDownloader\Enumerators;

enum RateExceedAction : int{
    case  CANCEL  = 1;
    case  QUE = 2;
}