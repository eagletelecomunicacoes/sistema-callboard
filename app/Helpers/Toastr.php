<?php
class Toastr
{
    public static function success($message)
    {
        $_SESSION['toastr_success'] = $message;
    }

    public static function error($message)
    {
        $_SESSION['toastr_error'] = $message;
    }

    public static function warning($message)
    {
        $_SESSION['toastr_warning'] = $message;
    }

    public static function info($message)
    {
        $_SESSION['toastr_info'] = $message;
    }

    public static function render()
    {
        $output = '';

        if (isset($_SESSION['toastr_success'])) {
            $output .= "<script>toastr.success('" . addslashes($_SESSION['toastr_success']) . "');</script>";
            unset($_SESSION['toastr_success']);
        }

        if (isset($_SESSION['toastr_error'])) {
            $output .= "<script>toastr.error('" . addslashes($_SESSION['toastr_error']) . "');</script>";
            unset($_SESSION['toastr_error']);
        }

        if (isset($_SESSION['toastr_warning'])) {
            $output .= "<script>toastr.warning('" . addslashes($_SESSION['toastr_warning']) . "');</script>";
            unset($_SESSION['toastr_warning']);
        }

        if (isset($_SESSION['toastr_info'])) {
            $output .= "<script>toastr.info('" . addslashes($_SESSION['toastr_info']) . "');</script>";
            unset($_SESSION['toastr_info']);
        }

        return $output;
    }
}
