<?php
declare(strict_types = 1);

namespace Innmind\OperatingSystem;

use Innmind\Server\Status\Server as ServerStatus;
use Innmind\Server\Control\Server as ServerControl;
use Innmind\TimeContinuum\Clock;

interface OperatingSystem
{
    public function clock(): Clock;
    public function filesystem(): Filesystem;
    public function status(): ServerStatus;
    public function control(): ServerControl;
    public function ports(): Ports;
    public function sockets(): Sockets;
    public function remote(): Remote;
    public function process(): CurrentProcess;
}
