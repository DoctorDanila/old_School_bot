import net.dv8tion.jda.api.JDABuilder;

import javax.security.auth.login.LoginException;

public class Main_Bot  {
    public static void main(String[] args) throws LoginException {
        String token = "";
        System.out.println("Если вы видите ошибку в логах, закройте их и она не будет мозолить вам глаза :)");
        JDABuilder builder = JDABuilder.createDefault(token);
        builder.addEventListeners(new DiscordBot_Listener());
        builder.build();
    }
}
