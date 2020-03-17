<?php $paths = $this->install_paths(); ?>
window.location.href='<?php e($paths['full']); ?>?feedlet&url='+window.encodeURIComponent(window.location.href);