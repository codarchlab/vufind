package org.dainst.index;
/**
 * Control number indexing routines.
 *
 * Copyright (C) Deutsches Archaeologisches Institut (DAI) 2017.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 2,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 */

import org.marc4j.marc.Record;

import org.solrmarc.index.SolrIndexer;

import org.vufind.index.ConfigManager;

import java.util.Iterator;
import java.util.List;
import java.util.LinkedList;
/**
 * Control number indexing routines.
 */
public class ControlNumberTools
{
    /**
     * Prefixes given fields with control number identifier (MARC field 003)
     * @param Record record current MARC record
     * @param String glue between combined fields
     * @param String tagList List of fields that should be prefixed
     * @return Prefixed fields specified in taglist
     */
    public List<String> prefixFieldsWithControlNumberIdentifier(final Record record, String glue, String tagList){

        List<String> result = new LinkedList<String>();
        String controlNumberIdentifier = SolrIndexer.instance().getFirstFieldVal(record, "003");
        List subfields = SolrIndexer.instance().getAllSubfieldsAsList(record, tagList, glue);

        if(controlNumberIdentifier != null && subfields != null)
        {
            Iterator subfieldsIter = subfields.iterator();
            String currentSubfield;
            while (subfieldsIter.hasNext()){
                currentSubfield = (String) subfieldsIter.next();
                result.add(controlNumberIdentifier + glue + currentSubfield);
            }
        }

        return result;
    }

    /**
     * Prefixes control number (MARC field 001) with control number identifier (MARC field 003)
     * @param Record record current MARC record
     * @param String glue between combined fields (Optional), defaults to "-"
     * @return Prefixed control number
     */
    public String prefixControlNumberWithControlNumberIdentifier(final Record record, String glue) {
        String controlNumber = SolrIndexer.instance().getFirstFieldVal(record, "001");
        String controlNumberIdentifier = SolrIndexer.instance().getFirstFieldVal(record, "003");

        if(controlNumber != null && controlNumberIdentifier != null) {
            return controlNumberIdentifier + glue + controlNumber;
        }
        else {
            return controlNumber;
        }

    }

    public String prefixControlNumberWithControlNumberIdentifier(final Record record) {
        return prefixControlNumberWithControlNumberIdentifier(record, "-");
    }

    /**
     * Prefixes control number (MARC field 001) with local prefix defined in zenon-config.ini
     * @param Record record current MARC record
     * @return Prefixed control number
     */
    public String addLocalRecordPrefixToControlNumber(final Record record) {
        String controlNumber = SolrIndexer.instance().getFirstFieldVal(record, "001");
        String prefix = ConfigManager.instance().getConfigSetting("zenon-config.ini", "Records", "localRecordPrefix");

        return prefix + controlNumber;
    }
}