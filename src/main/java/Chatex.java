import java.io.BufferedReader;
import java.io.IOException;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.io.Reader;
import java.net.URL;
import java.net.URLEncoder;
import java.nio.charset.Charset;
import java.nio.charset.StandardCharsets;

import org.json.JSONException;
import org.json.JSONObject;

public class Chatex {
    private static final String url = "https://afisha.live/bots/discord/old-school/chatex/bot.php?user=";

    public static JSONObject getChatex(String user, String text) throws IOException, JSONException{
        JSONObject json = readJsonFromUrl(url+ URLEncoder.encode(user, StandardCharsets.UTF_8)+"&text="+URLEncoder.encode(text, StandardCharsets.UTF_8));
        System.out.println(url+user+"&text="+text);
        return json;
    }

    private static String readAll(Reader rd) throws IOException {
        StringBuilder sb = new StringBuilder();
        int cp;
        while ((cp = rd.read()) != -1) {
            sb.append((char) cp);
        }
        return sb.toString();
    }

    public static JSONObject readJsonFromUrl(String url) throws IOException, JSONException {
        InputStream is = new URL(url).openStream();
        try {
            BufferedReader rd = new BufferedReader(new InputStreamReader(is, Charset.forName("UTF-8")));
            //String jsonText = readAll(rd);
            //JSONObject json = new JSONObject(jsonText);
            return null;
        } finally {
            is.close();
        }
    }
}
