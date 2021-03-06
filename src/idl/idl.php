<?php

include_once 'base.php';

/**
 * Possible values for 'format':
 *
 *   cpp:    generating .h and .cpp for implementing the functions.
 *   inc:    generating .inc for system/builtin_symbols.cpp
 *   test:   generating .h and .cpp for unit tests
 *   bridge: generating back bridge interface files
 *   param:  synchronize parameter types and names
 *   extmap: sep extension's prototype map
 */
$format = $argv[1];
$input = $argv[2];
$mode = '';

// format-mode: for "sep" detachable extensions
if (strpos($format, '-') > 0) {
  list($format, $mode) = explode('-', $format);
}

$test_header = '';
$test_impl = '';
$bridge_impl = '';
$header = '';
$impl = '';
$inc = '';
$dyns = array();
$map_header = '';
$map_impl = '';

switch ($format) {
case 'cpp':
  $header = $argv[3];
  $impl = $argv[4];
  break;
case 'inc':
  $inc = $argv[3];
  break;
case 'test':
  $test_header = $argv[3];
  $ext_header = substr($test_header, 5);
  $test_impl = $argv[4];
  break;
case 'bridge':
  $bridge_impl = $argv[3];
  break;
case 'param':
  $param_header = $argv[3];
  $param_impl = $argv[4];
  break;
case 'extmap':
  $map_header = $argv[3];
  $map_impl = $argv[4];
}

if (preg_match('/\.idl\.php/', $input)) {
  $name = preg_replace('/\.idl\.php/', '', $input);
} else {
  throw new Exception("wrong IDL or schema file $input");
}
require $input;

$name = preg_replace('|[/\.]|', '_', $name);
$NAME = strtoupper($name);
$Name = ucfirst($name);

$PREFIX = ($mode == 'sep') ? 'SEPEXT' : 'EXT';

/*****************************************************************************/
if ($header) {
  ($f = fopen($header, 'w')) || die("cannot open $header");

  fprintf($f,
          <<<EOT

#ifndef __${PREFIX}_${NAME}_H__
#define __${PREFIX}_${NAME}_H__

// >>>>>> Generated by idl.php. Do NOT modify. <<<<<<

#include <runtime/base/base_includes.h>

EOT
          );

  if (isset($preamble)) {
    fprintf($f, $preamble);
  }

  fprintf($f,
          <<<EOT

namespace HPHP {
///////////////////////////////////////////////////////////////////////////////


EOT
          );

  foreach ($funcs as $func) {
    generateFuncCPPHeader($func, $f);
  }
  foreach ($constants as $const) {
    generateConstCPPHeader($const, $f);
  }
  foreach ($classes as $class) {
    generateClassCPPHeader($class, $f);
  }
  fprintf($f,
          <<<EOT

///////////////////////////////////////////////////////////////////////////////
}

#endif // __${PREFIX}_${NAME}_H__

EOT
          );
  fclose($f);
}

/*****************************************************************************/
if ($impl) {
  ($f = fopen($impl, 'w')) || die("cannot open $impl");
  if ($mode == 'sep') {
    $inc_file = "\"ext_${name}.h\"";
  } else {
    $inc_file = "<runtime/ext/ext_${name}.h>";
  }
  fprintf($f,
          <<<EOT

#include $inc_file

namespace HPHP {
///////////////////////////////////////////////////////////////////////////////


EOT
          );
  foreach ($funcs as $func) {
    generateFuncCPPImplementation($func, $f);
  }
  fprintf($f,
          <<<EOT

///////////////////////////////////////////////////////////////////////////////
}

EOT
          );
}

/*****************************************************************************/
if ($inc) {
  ($f = fopen($inc, 'w')) || die("cannot open $inc");
  fprintf($f, "// %sgenerated by \"php idl.php inc ".
          "{input.idl.php} {output.inc}\"\n", '@');
  fprintf($f, "\n#if EXT_TYPE == 0\n");
  foreach ($funcs as $func) {
    generateFuncCPPInclude($func, $f);
  }
  fprintf($f, "\n#elif EXT_TYPE == 1\n");
  foreach ($constants as $const) {
    generateConstCPPInclude($const, $f);
  }
  fprintf($f, "\n#elif EXT_TYPE == 2\n");
  foreach ($classes as $class) {
    generateClassCPPInclude($class, $f);
  }
  fprintf($f, "\n#elif EXT_TYPE == 3\n");
  if ($dyns) {
    foreach ($dyns as $dyn) {
      fprintf($f, "\"%s\",", $dyn);
    }
  }
  $done = array();
  foreach ($funcs as $func) {
    if ($func['opt']) {
      if (!$done) {
        fprintf($f, "\n#elif EXT_TYPE == 4\n");
      }
      if (!isset($done[$func['opt']])) {
        generateFuncOptDecls($func, $f);
        $done[$func['opt']] = true;
      }
    }
  }
  fprintf($f, "\n");
  fprintf($f, "#endif\n");
}

/*****************************************************************************/
if ($test_header) {
  ($f = fopen($test_header, 'w')) || die("cannot open $test_header");
  fprintf($f,
          <<<EOT

#ifndef __TEST_${PREFIX}_${NAME}_H__
#define __TEST_${PREFIX}_${NAME}_H__

// >>>>>> Generated by idl.php. Do NOT modify. <<<<<<

#include <test/test_cpp_ext.h>

///////////////////////////////////////////////////////////////////////////////

class TestExt${Name} : public TestCppExt {
 public:
  virtual bool RunTests(const std::string &which);


EOT
          );
  foreach ($funcs as $func) {
    fprintf($f, "  bool test_".$func['name']."();\n");
  }
  foreach ($classes as $class) {
    fprintf($f, "  bool test_".$class['name']."();\n");
  }
  fprintf($f,
          <<<EOT
};

///////////////////////////////////////////////////////////////////////////////

#endif // __TEST_${PREFIX}_${NAME}_H__

EOT
          );
  fclose($f);
}

/*****************************************************************************/
if ($test_impl) {
  ($f = fopen($test_impl, 'w')) || die("cannot open $test_impl");
  if ($mode == 'sep') {
    $inc_file1 = "\"$test_header\"";
    $inc_file2 = "\"ext_${name}.h\"";
  } else {
    $inc_file1 = "<test/$test_header>";
    $inc_file2 = "<runtime/ext/ext_${name}.h>";
  }
  fprintf($f,
          <<<EOT

#include $inc_file1
#include $inc_file2

IMPLEMENT_SEP_EXTENSION_TEST($Name);
///////////////////////////////////////////////////////////////////////////////

bool TestExt${Name}::RunTests(const std::string &which) {
  bool ret = true;


EOT
  );

  foreach ($funcs as $func) {
    fprintf($f, "  RUN_TEST(test_".$func['name'].");\n");
  }
  foreach ($classes as $class) {
    fprintf($f, "  RUN_TEST(test_".$class['name'].");\n");
  }
  fprintf($f, <<<EOT

  return ret;
}

///////////////////////////////////////////////////////////////////////////////

EOT
          );

  foreach ($funcs + $classes as $item) {
    $item_name = $item['name'];
    fprintf($f, <<<EOT

bool TestExt${Name}::test_$item_name() {
  return Count(true);
}

EOT
          );
  }

  fclose($f);
}

/*****************************************************************************/
if ($bridge_impl) {
  ($f = fopen($bridge_impl, 'w')) || die("cannot open $bridge_impl");
  fprintf($f, "#include \"%s.h\"\n", $name);
  fprintf($f,
          <<<EOT
#include <stdio.h>
#include "complex_types.h"
#include <map>
#include <boost/scoped_array.hpp>
// Avoid duplicate definition warnings in the PHP headers
#undef PHP_ROUND_FUZZ
#undef PHP_BUILD_DATE
#undef PHP_UNAME
#undef ZEND_DEBUG
  // These PHP includes need to come after the HPHP ones, since they define some
  // macros that we don't want (like isset())
#include "php.h"
#include "zend_interfaces.h"
#include "zval.h"
#include "invoke.h"
#include "zend_API.h"

EOT
            );
  fprintf($f, "using namespace HPHP;\n\n");

  generatePHPBridgeModuleHeader($name, $f);

  foreach ($funcs as $func) {
    generatePHPBridgeImplementation($func, $f);
  }
  foreach ($classes as $class) {
    foreach ($class['methods'] as $method) {
      generatePHPBridgeImplementation($method, $f, $class['name'], $method['static']);
    }
  }
}

/*****************************************************************************/
if ($format == 'param') {
  replaceParams($param_header, true);
  replaceParams($param_impl, false);
}

/*****************************************************************************/
if ($format == 'profile') {
  $header = $argv[3];
  ($f = fopen($header, 'w')) || die("cannot open $header");
  if ($mode == 'sep') {
    $inc_file = "\"ext_${name}.h\"";
  } else {
    if ($name == "php_mcc") {
      $inc_file = "<runtime/ext/phpmcc/ext_${name}.h>";
    } else {
      $inc_file = "<runtime/ext/ext_${name}.h>";
    }
  }

  fprintf($f,
          <<<EOT

#ifndef __${PREFIX}PROFILE_${NAME}_H__
#define __${PREFIX}PROFILE_${NAME}_H__

// >>>>>> Generated by idl.php. Do NOT modify. <<<<<<

EOT
          );

  fprintf($f,
          <<<EOT

#include $inc_file

namespace HPHP {
///////////////////////////////////////////////////////////////////////////////


EOT
          );

  foreach ($funcs as $func) {
    generateFuncProfileHeader($func, $f);
  }
  fprintf($f,
          <<<EOT

///////////////////////////////////////////////////////////////////////////////
}

#endif // __${PREFIX}PROFILE_${NAME}_H__

EOT
          );
  fclose($f);
}

/*****************************************************************************/
if ($map_header) {
  ($f = fopen($map_header, 'w')) || die("cannot open $map_header");
  fprintf($f,
          <<<EOT

#include <util/base.h>
///////////////////////////////////////////////////////////////////////////////

extern "C" {
  extern const char **${name}_map[];
}

EOT
          );
  fclose($f);
}

/*****************************************************************************/
if ($map_impl) {
  ($f = fopen($map_impl, 'w')) || die("cannot open $map_impl");
  fprintf($f,
          <<<EOT

#include "extmap_${name}.h"
#include <compiler/analysis/type.h>

///////////////////////////////////////////////////////////////////////////////

static const char *${name}_extension_functions[] = {
#define S(n) (const char *)n
#define T(t) (const char *)HPHP::Type::KindOf ## t
#define EXT_TYPE 0
#include "${name}.inc"
  NULL,
};
#undef EXT_TYPE

static const char *${name}_extension_constants[] = {
#define EXT_TYPE 1
#include "${name}.inc"
  NULL,
};
#undef EXT_TYPE

static const char *${name}_extension_classes[] = {
#define EXT_TYPE 2
#include "${name}.inc"
  NULL,
};
#undef EXT_TYPE

static const char *${name}_extension_declared_dynamic[] = {
#define EXT_TYPE 3
#include "${name}.inc"
  NULL,
};
#undef EXT_TYPE

///////////////////////////////////////////////////////////////////////////////

const char **${name}_map[] = {
  ${name}_extension_functions,
  ${name}_extension_constants,
  ${name}_extension_classes,
  ${name}_extension_declared_dynamic
};

EOT
          );
  fclose($f);
}
