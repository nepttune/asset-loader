<?php

/**
 * This file is part of Nepttune (https://www.peldax.com)
 *
 * Copyright (c) 2018 Václav Pelíšek (info@peldax.com)
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <https://www.peldax.com>.
 */

declare(strict_types = 1);

namespace Nepttune\Component;

interface IScriptLists
{
    const SCRIPT_BODY = [
        '/node_modules/jquery/dist/jquery.min.js',
        '/node_modules/nette.ajax.js/nette.ajax.js',
        '/node_modules/magnific-popup/dist/jquery.magnific-popup.min.js',
        '/node_modules/photoswipe/dist/photoswipe.min.js',
        '/node_modules/photoswipe/dist/photoswipe-ui-default.min.js',
        '/node_modules/js-cookie/src/js.cookie.js',
        '/node_modules/nepttune/js/common.js'];
    const SCRIPT_BODY_ADMIN = [
        '/node_modules/bootstrap/dist/js/bootstrap.min.js',
        '/node_modules/admin-lte/dist/js/adminlte.min.js'];
    const SCRIPT_BODY_FRONT = [
        '/node_modules/bootstrap-beta/dist/js/bootstrap.bundle.min.js'];

    const SCRIPT_FORM = [
        '/node_modules/nette-forms/src/assets/netteForms.min.js',
        '/node_modules/nextras-forms/js/nextras.netteForms.js',
        '/node_modules/nas-ext-dependent-select-box/client-side/dependentSelectBox.js',
        '/node_modules/pickadate/lib/picker.js',
        '/node_modules/pickadate/lib/picker.date.js',
        '/node_modules/pickadate/lib/picker.time.js',
        '/node_modules/pickadate/lib//translations/cs_CZ.js',
        '/node_modules/select2/dist/js/select2.min.js',
        '/node_modules/icheck/icheck.min.js',
        '/node_modules/nepttune/js/coreValidator.js',
        '/node_modules/nepttune/js/form.js'];
    const SCRIPT_LIST = [
        '/node_modules/ublaboo-datagrid/assets/dist/datagrid.js',
        '/node_modules/ublaboo-datagrid/assets/dist/datagrid-spinners.js'];
    const SCRIPT_STAT = [
        '/node_modules/chart.js/dist/Chart.min.js',
        '/node_modules/nepttune/js/stat.js'];
}
