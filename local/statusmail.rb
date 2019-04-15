#!/usr/bin/env ruby
#
# Ruby script for sending status reports
#

require 'yaml' 
require 'mail'

dir = File.dirname(__FILE__) + '/'

conf = YAML.load_file(dir + "config/statusmail.yml")

Mail.defaults do
  delivery_method :smtp, conf['smtp']
end

result = "OK"
body = "\n\n--------------------------------\n\n"

conf['logs'].each do |log|
	body += "\n#{log['description']}:\n\n"
	ptn = dir + log['file_pattern']
	last_log = Dir[ptn].sort_by { |f| File.mtime(f) }.last()
	if last_log == nil
		body += "Error: No logfile found for pattern #{ptn}\n"
		result = "ERROR"
	elsif Time.new - File.mtime(last_log) > log['max_age'] * 24 * 3600
		body += "Error: Last logfile #{last_log} is older than #{log['max_age']} days\n"
		result = "ERROR"
	elsif File.foreach(last_log).grep(/#{log['error_pattern']}/).any?
		body += "Error: Last logfile #{last_log} contains errors:\n\n"
		File.foreach(last_log).grep(/#{log['error_pattern']}/).each do |l|
			body += l
		end
		result = "ERROR"
  elsif ptn.include? "import_" and !File.foreach(last_log).grep(/Done with all indexing, finishing writing records to solr/).any?
		body += "Error: Last logfile #{last_log} contains errors:\n\n"
    body += "Solr Indexing did not finish."
    result = "ERROR"
  else
		body += "OK: Last logfile #{last_log} is not older than #{log['max_age']} days and contains no errors\n"
	end
	body += "\n\n--------------------------------\n\n"
end

puts body

Mail.deliver do
  from     conf['from']
  to       conf['to']
  subject  "#{conf['subject']}: #{result}"
  body     body
end
