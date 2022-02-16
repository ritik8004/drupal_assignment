// Match the value from core.services.yml.
ini_set('session.gc_maxlifetime', 2000000);

// Disable session garbage collection, we do it via cron job.
ini_set('session.gc_probability', 0);
