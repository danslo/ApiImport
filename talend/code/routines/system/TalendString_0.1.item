// ============================================================================
//
// %GENERATED_LICENSE%
//
// ============================================================================
package routines;

import java.util.Random;
import java.util.Vector;

public class TalendString {

    /** Index of the first accent character **/
    private static final int MIN = 192;

    /** Index of the last accent character **/
    private static final int MAX = 255;

    /** used to save the link between with or without accents **/
    private static final Vector map = initMap();

    public static Vector getMap() {
        return map;
    }

    /**
     * return Replace the special character(e.g. <,>,& etc) within a string for XML file.
     * 
     * 
     * {talendTypes} String
     * 
     * {Category} TalendString
     * 
     * {param} string("") input: The string with the special character(s) need to be replaced.
     * 
     * {example} replaceSpecialCharForXML("<title>Empire <>Burlesque</title>") # <title>Empire &lt;&gt;Burlesque</title>
     */
    public static String replaceSpecialCharForXML(String input) {
        input = input.replaceAll("&", "&amp;"); //$NON-NLS-1$ //$NON-NLS-2$
        input = input.replaceAll("<", "&lt;"); //$NON-NLS-1$ //$NON-NLS-2$
        input = input.replaceAll(">", "&gt;"); //$NON-NLS-1$ //$NON-NLS-2$
        input = input.replaceAll("'", "&apos;"); //$NON-NLS-1$ //$NON-NLS-2$
        input = input.replaceAll("\"", "&quot;"); //$NON-NLS-1$ //$NON-NLS-2$
        return input;
    }

    /**
     * 
     */
    public static String checkCDATAForXML(String input) {
        if (input.startsWith("<![CDATA[") && input.endsWith("]]>")) { //$NON-NLS-1$ //$NON-NLS-2$
            return input;
        } else {
            return TalendString.replaceSpecialCharForXML(input);
        }
    }

    /**
     * getAsciiRandomString : Return a randomly generated String
     * 
     * 
     * {talendTypes} String
     * 
     * {Category} TalendString
     * 
     * {param} int(6) length: length of the String to return
     * 
     * {example} getAsciiRandomString(6) # Art34Z
     */
    public static String getAsciiRandomString(int length) {
        Random random = new Random();
        int cnt = 0;
        StringBuffer buffer = new StringBuffer();
        char ch;
        int end = 'z' + 1;
        int start = ' ';
        while (cnt < length) {
            ch = (char) (random.nextInt(end - start) + start);
            if (Character.isLetterOrDigit(ch)) {
                buffer.append(ch);
                cnt++;
            }
        }
        return buffer.toString();
    }

    /**
     * talendTrim: Returns a copy of the string, with leading and trailing specified char omitted.
     * 
     * 
     * {talendTypes} String
     * 
     * {Category} TalendString
     * 
     * {param} string("") origin: The original string need to be trimed.
     * 
     * {param} char(' ') padding_char: The padding char for triming.
     * 
     * {param} int(0) align: The alignment of the content in the original string. Positive int for right, negative int
     * for left and zero for center. Positive integer to trim the left part, zero to trim both the left and the right part, negative to trim the right part.
     * 
     * 
     * {example} talendTrim("$$talend open studio$$$$", '$', 0) # talend open studio
     */
    public static String talendTrim(String origin, char padding_char, int align) {
        if (null == origin) {
            return null;
        }
        String sPaddingChar = java.util.regex.Matcher.quoteReplacement(Character.toString(padding_char));

        if (align > 0) {// positive integer to trim left
            origin = origin.replaceAll("^" + sPaddingChar + "+", "");
        } else if (align == 0) {// zero to trim both left and right
            origin = origin.replaceAll("^" + sPaddingChar + "+", "");
            origin = origin.replaceAll(sPaddingChar + "+$", "");
        } else if (align < 0) {// negative integer to trim right
            origin = origin.replaceAll(sPaddingChar + "+$", "");
        }

        return origin;
    }

    /**
     * Initialisation of the map for the accents.
     */
    private static Vector initMap() {
        Vector result = new Vector();
        String car = null;

        car = new String("A"); //$NON-NLS-1$
        result.add(car); /* '\u00C0' alt-0192 */
        result.add(car); /* '\u00C1' alt-0193 */
        result.add(car); /* '\u00C2' alt-0194 */
        result.add(car); /* '\u00C3' alt-0195 */
        result.add(car); /* '\u00C4' alt-0196 */
        result.add(car); /* '\u00C5' alt-0197 */
        car = new String("AE"); //$NON-NLS-1$
        result.add(car); /* '\u00C6' alt-0198 */
        car = new String("C"); //$NON-NLS-1$
        result.add(car); /* '\u00C7' alt-0199 */
        car = new String("E"); //$NON-NLS-1$
        result.add(car); /* '\u00C8' alt-0200 */
        result.add(car); /* '\u00C9' alt-0201 */
        result.add(car); /* '\u00CA' alt-0202 */
        result.add(car); /* '\u00CB' alt-0203 */
        car = new String("I"); //$NON-NLS-1$
        result.add(car); /* '\u00CC' alt-0204 */
        result.add(car); /* '\u00CD' alt-0205 */
        result.add(car); /* '\u00CE' alt-0206 */
        result.add(car); /* '\u00CF' alt-0207 */
        car = new String("D"); //$NON-NLS-1$
        result.add(car); /* '\u00D0' alt-0208 */
        car = new String("N"); //$NON-NLS-1$
        result.add(car); /* '\u00D1' alt-0209 */
        car = new String("O"); //$NON-NLS-1$
        result.add(car); /* '\u00D2' alt-0210 */
        result.add(car); /* '\u00D3' alt-0211 */
        result.add(car); /* '\u00D4' alt-0212 */
        result.add(car); /* '\u00D5' alt-0213 */
        result.add(car); /* '\u00D6' alt-0214 */
        car = new String("*"); //$NON-NLS-1$
        result.add(car); /* '\u00D7' alt-0215 */
        car = new String("0"); //$NON-NLS-1$
        result.add(car); /* '\u00D8' alt-0216 */
        car = new String("U"); //$NON-NLS-1$
        result.add(car); /* '\u00D9' alt-0217 */
        result.add(car); /* '\u00DA' alt-0218 */
        result.add(car); /* '\u00DB' alt-0219 */
        result.add(car); /* '\u00DC' alt-0220 */
        car = new String("Y"); //$NON-NLS-1$
        result.add(car); /* '\u00DD' alt-0221 */
        car = new String("_"); //$NON-NLS-1$
        result.add(car); /* '\u00DE' alt-0222 */
        car = new String("B"); //$NON-NLS-1$
        result.add(car); /* '\u00DF' alt-0223 */
        car = new String("a"); //$NON-NLS-1$
        result.add(car); /* '\u00E0' alt-0224 */
        result.add(car); /* '\u00E1' alt-0225 */
        result.add(car); /* '\u00E2' alt-0226 */
        result.add(car); /* '\u00E3' alt-0227 */
        result.add(car); /* '\u00E4' alt-0228 */
        result.add(car); /* '\u00E5' alt-0229 */
        car = new String("ae"); //$NON-NLS-1$
        result.add(car); /* '\u00E6' alt-0230 */
        car = new String("c"); //$NON-NLS-1$
        result.add(car); /* '\u00E7' alt-0231 */
        car = new String("e"); //$NON-NLS-1$
        result.add(car); /* '\u00E8' alt-0232 */
        result.add(car); /* '\u00E9' alt-0233 */
        result.add(car); /* '\u00EA' alt-0234 */
        result.add(car); /* '\u00EB' alt-0235 */
        car = new String("i"); //$NON-NLS-1$
        result.add(car); /* '\u00EC' alt-0236 */
        result.add(car); /* '\u00ED' alt-0237 */
        result.add(car); /* '\u00EE' alt-0238 */
        result.add(car); /* '\u00EF' alt-0239 */
        car = new String("d"); //$NON-NLS-1$
        result.add(car); /* '\u00F0' alt-0240 */
        car = new String("n"); //$NON-NLS-1$
        result.add(car); /* '\u00F1' alt-0241 */
        car = new String("o"); //$NON-NLS-1$
        result.add(car); /* '\u00F2' alt-0242 */
        result.add(car); /* '\u00F3' alt-0243 */
        result.add(car); /* '\u00F4' alt-0244 */
        result.add(car); /* '\u00F5' alt-0245 */
        result.add(car); /* '\u00F6' alt-0246 */
        car = new String("/"); //$NON-NLS-1$
        result.add(car); /* '\u00F7' alt-0247 */
        car = new String("0"); //$NON-NLS-1$
        result.add(car); /* '\u00F8' alt-0248 */
        car = new String("u"); //$NON-NLS-1$
        result.add(car); /* '\u00F9' alt-0249 */
        result.add(car); /* '\u00FA' alt-0250 */
        result.add(car); /* '\u00FB' alt-0251 */
        result.add(car); /* '\u00FC' alt-0252 */
        car = new String("y"); //$NON-NLS-1$
        result.add(car); /* '\u00FD' alt-0253 */
        car = new String("_"); //$NON-NLS-1$
        result.add(car); /* '\u00FE' alt-0254 */
        car = new String("y"); //$NON-NLS-1$
        result.add(car); /* '\u00FF' alt-0255 */
        result.add(car); /* '\u00FF' alt-0255 */

        return result;
    }

    /**
     * removeAccents: remove accents from the string given.
     * 
     * 
     * {talendTypes} String
     * 
     * {Category} TalendString
     * 
     * {param} string("") text: Text to remove accents.
     * 
     * 
     * {example} removeAccents("Accès à la base")
     */
    public static String removeAccents(String text) {
        StringBuffer result = new StringBuffer();

        for (int bcl = 0; bcl < text.length(); bcl++) {
            char carVal = text.charAt(bcl);
            if (carVal >= 192 && carVal <= 255) {
                String newVal = (String) map.get(carVal - 192);
                result.append(newVal);
            } else {
                result.append(carVal);
            }
        }
        return result.toString();
    }
}
