import net.dv8tion.jda.api.entities.*;
import net.dv8tion.jda.api.events.message.guild.GuildMessageReceivedEvent;
import net.dv8tion.jda.api.hooks.ListenerAdapter;
import org.json.JSONException;
import java.io.IOException;

public class DiscordBot_Listener extends ListenerAdapter {

    @Override
    public void onGuildMessageReceived(GuildMessageReceivedEvent event) {

        if(event.getAuthor().isBot()) {
            return;
        }

        new ResultHandler(event.getMessage(),event);

    }

    private class ResultHandler
    {

        private final Message m;
        private final GuildMessageReceivedEvent event;

        private ResultHandler(Message m, GuildMessageReceivedEvent event)
        {
            this.m = m;
            this.event = event;
            try {
                NextStep (event.getAuthor().getName(),event.getMessage().getContentRaw());
            } catch (IOException e) {
                e.printStackTrace();
            }
        }

        public void NextStep (String user,String text) throws IOException, JSONException{
            Chatex.getChatex(user,text);
            return;
        }
    }

}
