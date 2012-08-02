#!/usr/bin/env python
# encoding: utf-8
"""
views.py

Created by Michael Smith on 2012-03-26.
Copyright (c) 2012 TrueAction. All rights reserved.

The MIT License
Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
"""

from __future__ import with_statement
from avro.datafile import DataFileReader
from avro.io import DatumReader
from json import dumps
from pyramid.view import view_config

##
# The sample view at cse/offer/create
#
@view_config(name="cse/offer/create")
def index_view(request):
	response = request.response
	if ('POST' != request.method):
		response.status = '405 Method Not Allowed'
	elif ('avro/binary' != request.content_type):
		response.status = '406 Not Acceptable'
	elif (0 >= request.content_length):
		response.status = '400 Bad Request'
	else:
		rec_reader = DatumReader()
		body_file = request.body_file_seekable
		with DataFileReader(body_file, rec_reader) as df_reader:
			print '\n'.join(dumps(v) for v in df_reader)
		response.text = u'Still testing'
	return response
