<?php
declare(strict_types = 1);

namespace Innmind\OperatingSystem;

use Innmind\Server\Status\Server as ServerStatus;
use Innmind\Server\Control\Server as ServerControl;
use Innmind\TimeContinuum\TimeContinuumInterface;

interface OperatingSystem
{
    public function clock(): TimeContinuumInterface;
    public function filesystem(): Filesystem;
    public function status(): ServerStatus;
    public function control(): ServerControl;
    public function ports(): Ports;
    public function sockets(): Sockets;
    public function remote(): Remote;
}
