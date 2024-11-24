<?php

namespace SmartDownloader\Enumerators;



enum ChunkSize: int{
    case MB_1 =  1048576;
    case MB_2  = 2097152;
    case MB_5 =  5242880;
    case MB_10 = 10485760;
    case MB_15 = 15728640;
}