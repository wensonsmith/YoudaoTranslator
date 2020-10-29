install: zip
	open youdaodict.alfredworkflow

zip:
	find . -type f -name '.DS_Store' -exec rm -rf '{}' \; && \
	cd ./src && \
	zip -r youdaodict.alfredworkflow . -x history -x cookie && \
	mv ./youdaodict.alfredworkflow ../

