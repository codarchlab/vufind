#!/usr/bin/env ruby

require 'marc'
require 'pp'
require 'logger'


def split_language_keys(record)

  if record['041'] and record['041']['a'] and record['041']['b']
    main_language_key_length = record['041']['a'].length
    if record['041']['b'].kind_of?(String) and
        record['041']['b'].length % main_language_key_length == 0 and
        record['041']['b'].length != main_language_key_length

      split_b = record['041']['b'].scan(/.{#{main_language_key_length}}/)
      updated_field = MARC::DataField.new('041',record['indiciator1'],record['indiciator2'])

      # copy all subfields except 'b' from original
      record['041'].each do |s|
        if(s.code != 'b')
          updated_field.append(s)
        end
      end

      # build new subfields 'b' from split values
      split_b.each do |value|
        updated_field.append(MARC::Subfield.new('b',value))
      end

      record.fields.delete(record['041'])
      record.append(updated_field)
    end
  end

  record
end

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
        record.fields.delete(record['003'])
      end
      record.append(MARC::ControlField.new('003', 'DE-2553'))
      record = split_language_keys(record)
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
