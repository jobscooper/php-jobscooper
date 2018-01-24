<?php
/**
 * Copyright 2014-18 Bryan Selner
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may
 * not use this file except in compliance with the License. You may obtain
 * a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations
 * under the License.
 */



//************************************************************************
//
//
//
//  Supported Job Site Plugin Types
//
//
//
//************************************************************************
const C__JOB_SEARCH_RESULTS_TYPE_SERVERSIDE_WEBPAGE__  = "SERVER_HTML";
const C__JOB_SEARCH_RESULTS_TYPE_CLIENTSIDE_WEBPAGE__  = "CLIENT_HTML";
const C__JOB_SEARCH_RESULTS_TYPE_JOBSAPI__ = "JOBAPI";
const C__JOB_SEARCH_RESULTS_TYPE_UNKNOWN__ = "ERROR_UNKNOWN_TYPE";

//************************************************************************
//
//
//
//  Supported Pagination Types
//
//
//
//************************************************************************
const C__PAGINATION_INFSCROLLPAGE_VIALOADMORE = "LOAD-MORE";
const C__PAGINATION_INFSCROLLPAGE_NOCONTROL= "INFINITE-SCROLL-NO-CONTROL";
const C__PAGINATION_INFSCROLLPAGE_PAGEDOWN= "INFINITE-SCROLL-PAGE-DOWN";
const C__PAGINATION_INFSCROLLPAGE_VIA_JS = "INFINITE-SCROLL-VIA-JAVASCRIPT";
const C__PAGINATION_PAGE_VIA_CALLBACK = "PAGE-CALLBACK";
const C__PAGINATION_PAGE_VIA_NEXTBUTTON = "NEXT-BUTTON";
const C__PAGINATION_PAGE_VIA_URL = "PAGE-VIA-URL";
const C__PAGINATION_NONE = "NONE";

//************************************************************************
//
//
//
//  Supported Plugin Configuration Flags
//
//
//
//************************************************************************

const C__JOB_USE_SELENIUM = 0x1;
const C__JOB_IGNORE_MISMATCHED_JOB_COUNTS = 0x2;

const C__JOB_KEYWORD_URL_PARAMETER_NOT_SUPPORTED = 0x10;
const C__JOB_LOCATION_URL_PARAMETER_NOT_SUPPORTED = 0x20;
const C__JOB_SETTINGS_URL_VALUE_REQUIRED = 0x40;
const C__JOB_RESULTS_SHOWN_IN_DATE_DESCENDING_ORDER = 0x80;

const C__JOB_LOCATION_REQUIRES_LOWERCASE = 0x100;
const C__JOB_KEYWORD_PARAMETER_SPACES_AS_DASHES = 0x200;
const C__JOB_KEYWORD_PARAMETER_SPACES_RAW_ENCODE = 0x400;
const C__JOB_KEYWORD_SUPPORTS_QUOTED_KEYWORDS = 0x800;

const C__JOB_PAGECOUNT_NOTAPPLICABLE__= 0x1000;
const C__JOB_DAYS_VALUE_NOTAPPLICABLE__ = 0x2000;
const C__JOB_ITEMCOUNT_NOTAPPLICABLE__ = 0x4000;
const C__JOB_ITEMCOUNT_STARTSATZERO__ = 0x8000;

//************************************************************************
//
//
//
//  Other Plugin Definition Constants
//
//
//
//************************************************************************

const C__TOTAL_ITEMS_UNKNOWN__ = 1111;
const C_JOB_MAX_RESULTS_PER_SEARCH = 1000;

define('TITLE_NEG_KWD_MATCH', 'No (Title Excluded Via Negative Keyword)');
define('NO_TITLE_MATCHES', 'No (Title Did Not Match Search Keywords))');

define('REXPR_PARTIAL_MATCH_URL_DOMAIN', '^https*.{3}[^\/]*');
define('REXPR_MATCH_URL_DOMAIN', '/^https*.{3}[^\/]*/');

