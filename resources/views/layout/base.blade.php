<!DOCTYPE html>
<!--
Template Name: Build Smooth - HTML Admin Dashboard Template
Author: Left4code
Website: http://www.left4code.com/
Contact: muhammadrizki@left4code.com
Purchase: https://themeforest.net/user/left4code/portfolio
Renew Support: https://themeforest.net/user/left4code/portfolio
License: You must have a valid license purchased only from themeforest(the above link) in order to legally use the theme for your project.
-->
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="{{ $dark_mode ? 'dark' : '' }}{{ $color_scheme != 'default' ? ' ' . $color_scheme : '' }}">
<!-- BEGIN: Head -->
<head>
    <meta charset="utf-8">
    <link href="{{ asset('dist/images/logo.svg') }}" rel="shortcut icon">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Build Smooth admin is super flexible, powerful, clean & modern responsive tailwind admin template with unlimited possibilities.">
    <meta name="keywords" content="admin template, Build Smooth Admin Template, dashboard template, flat admin template, responsive admin template, web app">
    <meta name="author" content="LEFT4CODE">

    @yield('head')

    <!-- BEGIN: CSS Assets-->
    <link rel="stylesheet" href="{{ mix('dist/css/app.css') }}" />
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!-- Lightbox2 CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/css/lightbox.min.css" rel="stylesheet">
    <!-- END: CSS Assets-->

    <style>
        .swal2-confirm {
            background-color: color-mix(in srgb, var(--swal2-confirm-button-background-color), var(--swal2-action-button-hover)) !important;
            color: white !important;
        }
        .swal2-cancel {
            background-color: color-mix(in srgb, var(--swal2-cancel-button-background-color), var(--swal2-action-button-hover)) !important;
            color: white !important;
        }
    </style>
</head>
<!-- END: Head -->

@yield('body')

</html>
