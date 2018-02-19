package org.dainst.index;

/**
 * Custom script for mapping localized field values to separate solr fields.
 *
 * @author Sebastian Cuy <sebastian.cuy@uni-koeln.de>
 * @author Simon Hohl <simon.hohl@dainst.org>
 */

import org.marc4j.marc.Record;
import org.marc4j.marc.VariableField;
import org.marc4j.marc.DataField;

import java.util.List;

public class LocalizationTools {

    public String getLocalizedFieldValue(
            Record record, String tag, String valSubField, String langSubField, String language
    ) {
        List<VariableField> fields = record.getVariableFields(tag);
        for (VariableField vf : fields) {
            DataField field = (DataField) vf;
            if (field.getSubfield(langSubField.charAt(0)).getData().equals(language)) {
                return field.getSubfield(valSubField.charAt(0)).getData();
            }
        }
        return "";
    }
}