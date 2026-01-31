import './bootstrap';
import './button-protection';
import './price-formatter';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

import ScannerFocusTrap from './scanner-focus';
window.ScannerFocusTrap = ScannerFocusTrap; // Make global if needed