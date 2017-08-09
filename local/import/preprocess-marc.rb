#!/usr/bin/env ruby

require 'marc'
require 'pp'
require 'logger'

name = File.basename(ARGV[0], File.extname(ARGV[0]))

vufind_home = ENV['VUFIND_HOME'] || '/usr/local/vufind'

marc_dir = vufind_home + "/local/import/mrc/"
Dir.mkdir marc_dir unless File.exists? marc_dir
log_dir = vufind_home + "/local/import/log/"
Dir.mkdir log_dir unless File.exists? log_dir
logger = Logger.new(log_dir + name + ".log")

# reading records from a batch file
reader = MARC::Reader.new(ARGV[0], :external_encoding => "UTF-8")

writer = MARC::Writer.new(marc_dir + name + ".mrc")
error_writer  = MARC::Writer.new(marc_dir + name + "failed.mrc")
for record in reader
  begin
    if record['001']
      if record['003']
        record['003'] = 'ZENON'
      else
        record.append(MARC::ControlField.new('003', 'ZENON'))
      end
      writer.write(record)
    else
      error_writer.write(record)
    end
  rescue => e
    logger.error "Error while writing record #{record['001']} to MARC: #{e.message}"
  end
end

error_writer.close()
writer.close()

puts "finished"
