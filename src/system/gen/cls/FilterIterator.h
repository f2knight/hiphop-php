/*
   +----------------------------------------------------------------------+
   | HipHop for PHP                                                       |
   +----------------------------------------------------------------------+
   | Copyright (c) 2010 Facebook, Inc. (http://www.facebook.com)          |
   +----------------------------------------------------------------------+
   | This source file is subject to version 3.01 of the PHP license,      |
   | that is bundled with this package in the file LICENSE, and is        |
   | available through the world-wide-web at the following url:           |
   | http://www.php.net/license/3_01.txt                                  |
   | If you did not receive a copy of the PHP license and are unable to   |
   | obtain it through the world-wide-web, please send a note to          |
   | license@php.net so we can mail you a copy immediately.               |
   +----------------------------------------------------------------------+
*/
// @generated by HipHop Compiler

#ifndef __GENERATED_cls_FilterIterator_h189f6b17__
#define __GENERATED_cls_FilterIterator_h189f6b17__

#include <cls/FilterIterator.fw.h>
#include <cls/OuterIterator.h>

namespace HPHP {
///////////////////////////////////////////////////////////////////////////////

/* SRC: classes/iterator.php line 901 */
FORWARD_DECLARE_CLASS(FilterIterator);
class c_FilterIterator : public ExtObjectData {
  public:

  // Properties

  // Class Map
  virtual bool o_instanceof(CStrRef s) const;
  DECLARE_CLASS_COMMON(FilterIterator, FilterIterator)
  DECLARE_INVOKE_EX(FilterIterator, FilterIterator, ObjectData)

  // DECLARE_STATIC_PROP_OPS
  public:
  #define OMIT_JUMP_TABLE_CLASS_STATIC_GETINIT_FilterIterator 1
  #define OMIT_JUMP_TABLE_CLASS_STATIC_GET_FilterIterator 1
  #define OMIT_JUMP_TABLE_CLASS_STATIC_LVAL_FilterIterator 1
  #define OMIT_JUMP_TABLE_CLASS_CONSTANT_FilterIterator 1

  // DECLARE_INSTANCE_PROP_OPS
  public:
  #define OMIT_JUMP_TABLE_CLASS_GETARRAY_FilterIterator 1
  #define OMIT_JUMP_TABLE_CLASS_SETARRAY_FilterIterator 1
  #define OMIT_JUMP_TABLE_CLASS_realProp_FilterIterator 1
  #define OMIT_JUMP_TABLE_CLASS_realProp_PRIVATE_FilterIterator 1

  // DECLARE_INSTANCE_PUBLIC_PROP_OPS
  public:
  #define OMIT_JUMP_TABLE_CLASS_realProp_PUBLIC_FilterIterator 1

  // DECLARE_COMMON_INVOKE
  static bool os_get_call_info(MethodCallPackage &mcp, int64 hash = -1);
  #define OMIT_JUMP_TABLE_CLASS_STATIC_INVOKE_FilterIterator 1
  virtual bool o_get_call_info(MethodCallPackage &mcp, int64 hash = -1);

  public:
  void init();
};
extern struct ObjectStaticCallbacks cw_FilterIterator;
Object co_FilterIterator(CArrRef params, bool init = true);
Object coo_FilterIterator();

///////////////////////////////////////////////////////////////////////////////
}

#endif // __GENERATED_cls_FilterIterator_h189f6b17__
