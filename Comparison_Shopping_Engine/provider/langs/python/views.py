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

from pyramid.view import view_config
from avro import io, datafile

@view_config(name="cse/offer/create")
def index_view(request):
	response = request.response
	if ('POST' != request.method):
		response.status_int = 405
		response.status = 'Method Not Allowed'
	elif ('avro/binary' != request.content_type):
		response.status_int = 406
		response.status = 'Not Acceptable'
	else:
		rec_reader = io.DatumReader()
		body_file = request.body_file_seekable
		df_reader = datafile.DataFileReader(
			body_file,
			rec_reader,
		)
		response.text = 'Still testing'
	return response
