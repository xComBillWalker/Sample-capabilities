#!/usr/bin/env python
# encoding: utf-8
"""
tests.py

Created by Michael Smith on 2012-03-26.
Copyright (c) 2012 TrueAction. All rights reserved.

The MIT License
Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
"""
from __future__ import with_statement
import unittest
from pyramid import testing
from os.path import getsize

class CSEViewsUnitTest(unittest.TestCase):
	def test_cse_bad_method(self):
		"""Send an unsupported http method."""
		from views import index_view
		request = testing.DummyRequest()
		result = index_view(request)
		self.assertEqual(result.status_int, 405, result.status_int)

	def test_cse_bad_contenttype(self):
		"""Send an unsupported http content-type header."""
		from views import index_view
		request = testing.DummyRequest(post={}, content_type='text/javascript')
		result = index_view(request)
		self.assertEqual(result.status_int, 406, result.status_int)

	def test_cse_empty_body(self):
		"""Send a POST with an empty request body."""
		from views import index_view
		request = testing.DummyRequest(post={}, content_type='avro/binary')
		result = index_view(request)
		self.assertEqual(result.status_int, 400, result.status_int)

	def test_cse_empty_avro(self):
		"""Send a POST with a schema-complete empty request."""
		from views import index_view
		request = testing.DummyRequest(post={}, content_type='avro/binary')
		request.content_length = getsize('tests/empty.avro')
		with open('tests/empty.avro', 'rb') as request.body_file_seekable:
			result = index_view(request)
		self.assertEqual(result.status_int, 200, result.status_int)

class CSEFunctionalTests(unittest.TestCase):
	def setUp(self):
		from application import main
		app = main()
		from webtest import TestApp
		self.testapp = TestApp(app)

	def test_it(self):
		"""Request the cse schema"""
		res = self.testapp.get('/avpr/cse.avpr', status=200)
		self.assertEqual(2612, res.content_length)
